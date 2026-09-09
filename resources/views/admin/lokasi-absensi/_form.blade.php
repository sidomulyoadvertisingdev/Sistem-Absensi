@php
    $lat = old('latitude', $location->latitude ?? '');
    $lng = old('longitude', $location->longitude ?? '');
    $radius = old('radius', $location->radius ?? \App\Models\AttendanceLocation::MAX_RADIUS);
    $aktif = old('aktif', $location->aktif ?? true);
    $selected = collect(old('users', $selectedUsers ?? []))->map(fn ($v) => (int) $v)->all();
    $gmapsKey = config('services.google_maps.key');
@endphp

@push('styles')
@unless($gmapsKey)
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="" />
@endunless
<style>
    #map-lokasi { height: 360px; border-radius: 6px; z-index: 0; }
    .pac-container { z-index: 20000; }
</style>
@endpush

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Nama Lokasi <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control"
                       value="{{ old('nama', $location->nama ?? '') }}"
                       placeholder="Contoh: Kantor Pusat / Gudang / SM Percetakan" required>
            </div>

            <div class="form-group">
                <label>Alamat (Opsional)</label>
                <textarea name="alamat" id="input-alamat" class="form-control" rows="2"
                          placeholder="Alamat lengkap lokasi">{{ old('alamat', $location->alamat ?? '') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-6">
                    <label>Latitude <span class="text-danger">*</span></label>
                    <input type="text" name="latitude" id="input-lat" class="form-control"
                           value="{{ $lat }}" placeholder="-6.200000" required>
                </div>
                <div class="form-group col-6">
                    <label>Longitude <span class="text-danger">*</span></label>
                    <input type="text" name="longitude" id="input-lng" class="form-control"
                           value="{{ $lng }}" placeholder="106.816666" required>
                </div>
            </div>

            <div class="form-group">
                <label>Radius (meter) <span class="text-danger">*</span></label>
                <input type="number" name="radius" id="input-radius" class="form-control"
                       value="{{ $radius }}" min="10" max="{{ \App\Models\AttendanceLocation::MAX_RADIUS }}" required>
                <small class="text-muted">
                    Maksimal {{ \App\Models\AttendanceLocation::MAX_RADIUS }} meter. Karyawan hanya bisa absen di dalam radius ini.
                </small>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="input-aktif"
                           name="aktif" value="1" {{ $aktif ? 'checked' : '' }}>
                    <label class="custom-control-label" for="input-aktif">Lokasi aktif</label>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <label>Pilih Titik di Peta</label>

            @if($gmapsKey)
                <input type="text" id="map-search" class="form-control mb-2"
                       placeholder="Cari alamat / nama tempat…" autocomplete="off">
            @endif

            <div id="map-lokasi" class="mb-2"></div>
            <small class="text-muted d-block mb-3">
                @if($gmapsKey)
                    Cari alamat di atas atau klik pada peta untuk menetapkan titik. Lingkaran menunjukkan radius absensi.
                @else
                    Klik pada peta untuk menetapkan titik lokasi. Lingkaran menunjukkan radius absensi.
                @endif
            </small>

            <label>Karyawan yang Boleh Absen di Lokasi Ini</label>
            <select name="users[]" class="form-control" multiple size="10">
                @foreach($karyawan as $k)
                    <option value="{{ $k->id }}" {{ in_array($k->id, $selected, true) ? 'selected' : '' }}>
                        {{ $k->name }}@if($k->nik) — {{ $k->nik }}@endif
                    </option>
                @endforeach
            </select>
            <small class="text-muted">
                Tahan Ctrl / Cmd untuk memilih lebih dari satu. Karyawan tanpa lokasi tidak bisa absen dari aplikasi.
            </small>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan
        </button>
        <a href="{{ route('admin.lokasi-absensi.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</form>

@push('scripts')
<script>
window.LokasiMapConfig = {
    lat: parseFloat(@json((string) $lat)) || null,
    lng: parseFloat(@json((string) $lng)) || null,
    fallbackLat: -6.200000,
    fallbackLng: 106.816666
};

function lokasiCurrentRadius() {
    var el = document.getElementById('input-radius');
    var r = parseInt(el && el.value, 10);
    return (r && r > 0) ? r : 200;
}
</script>

@if($gmapsKey)
{{-- ================= GOOGLE MAPS ================= --}}
<script>
function initLokasiMap() {
    var cfg = window.LokasiMapConfig;
    var latInput = document.getElementById('input-lat');
    var lngInput = document.getElementById('input-lng');
    var radiusInput = document.getElementById('input-radius');
    var hasPoint = !!(cfg.lat && cfg.lng);
    var start = { lat: hasPoint ? cfg.lat : cfg.fallbackLat, lng: hasPoint ? cfg.lng : cfg.fallbackLng };

    var map = new google.maps.Map(document.getElementById('map-lokasi'), {
        center: start,
        zoom: hasPoint ? 18 : 12,
        mapTypeId: 'hybrid',
        streetViewControl: false,
        mapTypeControl: true
    });

    var marker = new google.maps.Marker({ map: map, position: start, draggable: true, visible: hasPoint });
    var circle = new google.maps.Circle({
        map: map, center: start, radius: lokasiCurrentRadius(),
        fillColor: '#007bff', fillOpacity: 0.12, strokeColor: '#007bff', strokeWeight: 1,
        visible: hasPoint
    });

    function setPoint(lat, lng, recenter) {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        var pos = { lat: lat, lng: lng };
        marker.setPosition(pos); marker.setVisible(true);
        circle.setCenter(pos); circle.setRadius(lokasiCurrentRadius()); circle.setVisible(true);
        if (recenter) { map.setCenter(pos); map.setZoom(Math.max(map.getZoom(), 18)); }
    }

    if (hasPoint) setPoint(cfg.lat, cfg.lng, false);

    map.addListener('click', function (e) { setPoint(e.latLng.lat(), e.latLng.lng(), false); });
    marker.addListener('dragend', function (e) { setPoint(e.latLng.lat(), e.latLng.lng(), false); });
    radiusInput.addEventListener('input', function () { circle.setRadius(lokasiCurrentRadius()); });

    [latInput, lngInput].forEach(function (el) {
        el.addEventListener('change', function () {
            var lat = parseFloat(latInput.value), lng = parseFloat(lngInput.value);
            if (lat && lng) setPoint(lat, lng, true);
        });
    });

    var searchEl = document.getElementById('map-search');
    if (searchEl && google.maps.places) {
        var ac = new google.maps.places.Autocomplete(searchEl, { fields: ['geometry', 'formatted_address', 'name'] });
        ac.addListener('place_changed', function () {
            var place = ac.getPlace();
            if (!place.geometry || !place.geometry.location) return;
            setPoint(place.geometry.location.lat(), place.geometry.location.lng(), true);
            var alamatEl = document.getElementById('input-alamat');
            if (alamatEl && !alamatEl.value) alamatEl.value = place.formatted_address || place.name || '';
        });
        searchEl.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
    }
}
window.initLokasiMap = initLokasiMap;
</script>
<script async
        src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places&loading=async&callback=initLokasiMap"></script>
@else
{{-- ================= OPENSTREETMAP (LEAFLET) FALLBACK ================= --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
(function () {
    var mapEl = document.getElementById('map-lokasi');
    if (!mapEl || typeof L === 'undefined') return;

    var cfg = window.LokasiMapConfig;
    var latInput = document.getElementById('input-lat');
    var lngInput = document.getElementById('input-lng');
    var radiusInput = document.getElementById('input-radius');
    var hasPoint = !!(cfg.lat && cfg.lng);
    var startLat = hasPoint ? cfg.lat : cfg.fallbackLat;
    var startLng = hasPoint ? cfg.lng : cfg.fallbackLng;

    var map = L.map('map-lokasi').setView([startLat, startLng], hasPoint ? 17 : 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var marker = null, circle = null;

    function setPoint(lat, lng) {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        if (!marker) {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) { var p = e.target.getLatLng(); setPoint(p.lat, p.lng); });
        } else {
            marker.setLatLng([lat, lng]);
        }
        if (!circle) {
            circle = L.circle([lat, lng], { radius: lokasiCurrentRadius(), color: '#007bff' }).addTo(map);
        } else {
            circle.setLatLng([lat, lng]); circle.setRadius(lokasiCurrentRadius());
        }
    }

    if (hasPoint) setPoint(startLat, startLng);

    map.on('click', function (e) { setPoint(e.latlng.lat, e.latlng.lng); });
    radiusInput.addEventListener('input', function () { if (circle) circle.setRadius(lokasiCurrentRadius()); });

    [latInput, lngInput].forEach(function (el) {
        el.addEventListener('change', function () {
            var lat = parseFloat(latInput.value), lng = parseFloat(lngInput.value);
            if (lat && lng) { setPoint(lat, lng); map.setView([lat, lng], 17); }
        });
    });

    setTimeout(function () { map.invalidateSize(); }, 200);
})();
</script>
@endif
@endpush
