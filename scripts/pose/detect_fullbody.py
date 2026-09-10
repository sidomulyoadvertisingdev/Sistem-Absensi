#!/usr/bin/env python3
"""
Deteksi "badan penuh" untuk absensi kios.

Dipanggil Laravel:  python3 detect_fullbody.py <path_gambar>
Output (stdout, JSON satu baris):

  { "available": true|false,
    "full_body": true|false,
    "score": 0.0-1.0,
    "reasons": ["kaki_tidak_terlihat", ...],
    "person_count": 0|1,
    "coverage": { "head_y": 0.12, "feet_y": 0.94 } }

`available:false` berarti library/model tidak tersedia -> aplikasi jatuh ke foto manual.
"""

import sys
import json


def emit(payload):
    sys.stdout.write(json.dumps(payload))
    sys.stdout.flush()
    sys.exit(0)


def not_full(reasons, score=0.0, person_count=0, coverage=None):
    emit({
        "available": True,
        "full_body": False,
        "score": round(float(score), 3),
        "reasons": reasons,
        "person_count": person_count,
        "coverage": coverage or {},
    })


def main():
    if len(sys.argv) < 2:
        not_full(["no_input"])

    image_path = sys.argv[1]

    try:
        import cv2  # noqa
        import mediapipe as mp  # noqa
    except Exception as exc:  # library belum di-install di server
        emit({"available": False, "reason": "mediapipe_not_installed", "detail": str(exc)})

    img = cv2.imread(image_path)
    if img is None:
        not_full(["bad_image"])

    rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)

    mp_pose = mp.solutions.pose
    with mp_pose.Pose(
        static_image_mode=True,
        model_complexity=1,
        min_detection_confidence=0.5,
    ) as pose:
        res = pose.process(rgb)

    if not res.pose_landmarks:
        not_full(["tidak_terdeteksi_orang"])

    lm = res.pose_landmarks.landmark
    P = mp_pose.PoseLandmark

    def group_best(idxs):
        """visibility terbaik + y-nya, hanya kalau titik ada di dalam frame."""
        best_v, best_y = 0.0, None
        for i in idxs:
            p = lm[i.value]
            inside = (-0.03 <= p.x <= 1.03) and (-0.03 <= p.y <= 1.03)
            v = p.visibility if inside else 0.0
            if v > best_v:
                best_v, best_y = v, p.y
        return best_v, best_y

    groups = {
        "kepala":  [P.NOSE, P.LEFT_EYE, P.RIGHT_EYE, P.LEFT_EAR, P.RIGHT_EAR],
        "bahu":    [P.LEFT_SHOULDER, P.RIGHT_SHOULDER],
        "pinggul": [P.LEFT_HIP, P.RIGHT_HIP],
        "lutut":   [P.LEFT_KNEE, P.RIGHT_KNEE],
        "kaki":    [P.LEFT_ANKLE, P.RIGHT_ANKLE],
    }

    TH = 0.5
    scores, ys, detected = {}, {}, {}
    for name, idxs in groups.items():
        v, y = group_best(idxs)
        scores[name] = v
        ys[name] = y
        detected[name] = v >= TH

    # Wajib: kepala, bahu, pinggul, lutut. Pergelangan kaki (kaki) OPSIONAL —
    # supaya user tidak perlu mundur terlalu jauh; cukup sampai lutut kelihatan.
    reasons = []
    for name in ("kepala", "bahu", "pinggul", "lutut"):
        if not detected[name]:
            reasons.append(f"{name}_tidak_terlihat")

    head_y = ys["kepala"]
    feet_y = ys["kaki"]
    knee_y = ys["lutut"]

    head_ok = detected["kepala"] and head_y is not None and head_y <= 0.50

    # Bagian bawah dianggap cukup bila:
    #  - pergelangan kaki terlihat & cukup ke bawah (>= 0.55), ATAU
    #  - minimal lutut terlihat & cukup ke bawah (>= 0.68).
    lower_ok = (
        (detected["kaki"] and feet_y is not None and feet_y >= 0.55)
        or (detected["lutut"] and knee_y is not None and knee_y >= 0.68)
    )

    if not lower_ok:
        reasons.append("mundur_agar_kaki_masuk_frame")
    if detected["kepala"] and not head_ok:
        reasons.append("turunkan_kamera_atau_maju_sedikit")

    full_body = bool(
        detected["kepala"] and detected["bahu"] and detected["pinggul"]
        and detected["lutut"] and head_ok and lower_ok
    )

    # Skor = kepercayaan terendah dari bagian yang WAJIB (kaki opsional).
    required_scores = [scores[n] for n in ("kepala", "bahu", "pinggul", "lutut")]
    score = min(required_scores) if required_scores else 0.0

    emit({
        "available": True,
        "full_body": full_body,
        "score": round(float(score), 3),
        "reasons": [] if full_body else reasons,
        "person_count": 1,
        "coverage": {
            "head_y": None if head_y is None else round(float(head_y), 3),
            "feet_y": None if feet_y is None else round(float(feet_y), 3),
        },
    })


if __name__ == "__main__":
    try:
        main()
    except SystemExit:
        raise
    except Exception as exc:  # jangan pernah crash tanpa JSON
        sys.stdout.write(json.dumps({
            "available": True,
            "full_body": False,
            "score": 0.0,
            "reasons": ["exception"],
            "person_count": 0,
            "error": str(exc),
        }))
        sys.exit(0)
