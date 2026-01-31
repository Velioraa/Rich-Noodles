<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rich Noodles - Admin Dashboard</title>
    <link rel="website icon" type="png" href="asset/Richa Mart.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6D9DC5;
            --primary-light: #A7C6D9;
            --primary-dark: #4A7BA6;
            --secondary: #2D3047;
            --accent: #FFD166;
            --light: #F8F9FA;
            --dark: #212529;
            --gray: #6C757D;
            --success: #28A745;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f2ff 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            padding: 15px 5%;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo img {
            height: 50px;
            transition: var(--transition);
        }
        
        .logo:hover img {
            transform: scale(1.05);
        }
        
        .login-button {
            background: var(--primary);
            color: white;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 8px rgba(109, 157, 197, 0.3);
        }
        
        .login-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 157, 197, 0.4);
        }
        
        .main-container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 5%;
            text-align: center;
        }
        
        .hero {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 0;
        }
        
        .hero-image {
            width: 180px;
            margin: 0 auto 20px;
            filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.1));
            transition: var(--transition);
        }
        
        .hero-image:hover {
            transform: scale(1.05);
        }
        
        .hero h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--primary-dark);
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto 30px;
            color: var(--gray);
            font-weight: 400;
        }
        
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin-top: 40px;
            max-width: 1000px;
        }
        
        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            width: 280px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: left;
            border-top: 4px solid var(--primary-light);
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
            border-top: 4px solid var(--primary);
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            color: var(--primary-dark);
            font-size: 1.5rem;
        }
        
        .feature-card h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--secondary);
        }
        
        .feature-card p {
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .footer {
            padding: 25px 5%;
            background: var(--secondary);
            color: white;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        /* Animasi tambahan untuk soft blue theme */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .floating {
            animation: float 5s ease-in-out infinite;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                padding: 12px 4%;
            }
            
            .logo-text {
                font-size: 1.3rem;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .features {
                gap: 20px;
            }
            
            .feature-card {
                width: 100%;
                max-width: 350px;
            }
        }
        
        @media (max-width: 480px) {
            .logo-text {
                display: none;
            }
            
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .hero-image {
                width: 140px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="asset/Richa Mart.png" alt="Rich Noodles">
        </div>
        <button class="login-button" onclick="window.location.href='login.php'">LOGIN</button>
    </div>

    <div class="main-container">
        <div class="hero">
            <img src="asset/Richa Mart.png" alt="Rich Noodles" class="hero-image floating">
            <h1>Selamat Datang di Admin Rich Noodles!</h1>
            <p>Nikmati kemudahan mengelola semua kebutuhan toko Anda di satu tempat. Sebagai pusat mie instan terbaik, Rich Noodles menyediakan berbagai pilihan produk.</p>
            
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">📦</div>
                    <h3>Kelola Inventori</h3>
                    <p>Pantau dan kelola stok produk dengan mudah dan real-time.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Transaksi Cepat</h3>
                    <p>Proses penjualan dengan sistem kasir yang efisien dan akurat.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Laporan Lengkap</h3>
                    <p>Analisis penjualan dan kinerja toko dengan laporan detail.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            <p>© 2025 Rich Noodles - Solusi Terbaik untuk Bisnis Mie Instan Anda</p>
        </div>
    </div>
</body>
</html>