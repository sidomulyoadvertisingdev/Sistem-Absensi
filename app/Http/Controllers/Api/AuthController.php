use Illuminate\Support\Facades\Auth;

public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'message' => 'Email atau password salah'
        ], 401);
    }

    $user = Auth::user();

    // hapus token lama (opsional tapi disarankan)
    $user->tokens()->delete();

    $token = $user->createToken('frontend')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
    ]);
}
