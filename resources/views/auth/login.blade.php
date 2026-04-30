<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMK PERINTIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 
                            800: '#1e40af', 900: '#1e3a8a', 950: '#172554',
                        },
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-primary-50 text-gray-900 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-8 bg-primary-600 text-white text-center flex flex-col items-center">
            <div class="w-24 h-24 mb-4 flex items-center justify-center">
                <img src="{{ asset('assets/logo_perintis.png') }}" alt="Logo" class="max-w-full max-h-full object-contain">
            </div>
            <h1 class="text-3xl font-bold">SMK PERINTIS</h1>
            <p class="mt-2 text-primary-100 italic">Sistem Absensi Siswa</p>
        </div>
        
        <div class="p-8">
            <form action="{{ url('/login') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" required class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 transition" placeholder="admin@perintis.sch.id">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" required class="mt-1 block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500 transition" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-lg shadow-lg shadow-primary-200 transform transition active:scale-95">
                    Masuk ke Sistem
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-100 text-center text-sm text-gray-500">
                &copy; {{ \Carbon\Carbon::now()->year }} SMK PERINTIS Kab. Bandung
            </div>
        </div>
    </div>
</body>
</html>
