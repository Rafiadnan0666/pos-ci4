<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>POS Login - Outdoor Gear Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { neo: { yellow: '#FFDE4D', cyan: '#06B6D4', orange: '#F97316', green: '#22C55E', red: '#EF4444', white: '#F4F2EE', black: '#000000' } }, fontFamily: { heading: ['"Space Grotesk"', 'Inter', 'sans-serif'] }, boxShadow: { neo: '4px 4px 0px 0px rgba(0,0,0,1)' } } } }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
</head>
<body class="min-h-screen" style="background:#06B6D4;font-family:Inter,system-ui,sans-serif;display:flex;align-items:center;justify-content:center;padding:1rem;">
    <div class="w-full max-w-md">
        <div style="background:#fff;border:4px solid #000;box-shadow:4px 4px 0px 0px rgba(0,0,0,1);padding:1.5rem;">
            <div class="text-center mb-6">
                <span style="font-size:4rem;display:block;margin-bottom:.5rem;">🏕️</span>
                <h1 style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:800;text-transform:uppercase;font-size:1.875rem;letter-spacing:-.02em;">POS LOGIN</h1>
                <p style="font-size:.875rem;margin-top:.25rem;opacity:.7;font-weight:700;">Outdoor Gear Store - Staff Only</p>
            </div>

            <?php if (session()->has('error')): ?>
            <div style="background:#EF4444;color:#fff;border:4px solid #000;padding:.75rem;margin-bottom:1rem;font-weight:700;font-size:.875rem;">
                <?= session('error') ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('pos/authenticate') ?>" method="POST">
                <div class="space-y-4">
                    <div>
                        <label style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;font-size:.875rem;display:block;margin-bottom:.25rem;">EMAIL</label>
                        <input type="email" name="email" required
                               style="width:100%;padding:.75rem 1rem;border:4px solid #000;background:#fff;font-weight:700;font-size:.875rem;"
                               onfocus="this.style.boxShadow='4px 4px 0px 0px rgba(0,0,0,1)';this.style.background='#FFDE4D';"
                               onblur="this.style.boxShadow='none';this.style.background='#fff';"
                               placeholder="admin@outdoor.com" />
                    </div>
                    <div>
                        <label style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;font-size:.875rem;display:block;margin-bottom:.25rem;">PASSWORD</label>
                        <input type="password" name="password" required
                               style="width:100%;padding:.75rem 1rem;border:4px solid #000;background:#fff;font-weight:700;font-size:.875rem;"
                               onfocus="this.style.boxShadow='4px 4px 0px 0px rgba(0,0,0,1)';this.style.background='#FFDE4D';"
                               onblur="this.style.boxShadow='none';this.style.background='#fff';"
                               placeholder="Enter password" />
                    </div>
                    <button type="submit" style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;text-transform:uppercase;font-size:1rem;padding:.75rem 1.5rem;border:4px solid #000;box-shadow:4px 4px 0px 0px rgba(0,0,0,1);cursor:pointer;background:#06B6D4;color:#fff;width:100%;"
                            onmouseover="this.style.boxShadow='2px 2px 0px 0px #000';this.style.translate='2px 2px'"
                            onmouseout="this.style.boxShadow='4px 4px 0px 0px rgba(0,0,0,1)';this.style.translate='0'">LOGIN</button>
                </div>
            </form>

            <div style="border-top:4px solid #000;margin:1rem 0;"></div>
            <p style="text-align:center;font-size:.75rem;opacity:.6;font-weight:700;">Demo: admin@outdoor.com / password123</p>
        </div>
        <p style="text-align:center;margin-top:1rem;">
            <a href="<?= base_url('/products') ?>" style="color:#fff;font-weight:700;font-size:.875rem;text-decoration:underline;">← Back to Store</a>
        </p>
    </div>
</body>
</html>
