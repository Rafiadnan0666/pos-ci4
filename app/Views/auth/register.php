<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - Outdoor Gear Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { neo: { yellow: '#FFDE4D', cyan: '#06B6D4', orange: '#F97316', green: '#22C55E', red: '#EF4444', white: '#F4F2EE', black: '#000000' } }, fontFamily: { heading: ['"Space Grotesk"', 'Inter', 'sans-serif'] }, boxShadow: { neo: '4px 4px 0px 0px rgba(0,0,0,1)' } } } }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
</head>
<body class="bg-[#F97316] min-h-screen flex items-center justify-center p-4" style="font-family:Inter,system-ui,sans-serif;">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <span class="text-6xl block mb-2">🏔️</span>
            <h1 style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:800;text-transform:uppercase;font-size:2.25rem;color:#fff;letter-spacing:-.02em;">JOIN THE ADVENTURE</h1>
            <p style="font-weight:700;color:#fff;opacity:.8;margin-top:.25rem;font-size:.875rem;">Create your account</p>
        </div>

        <div style="background:#fff;border:4px solid #000;box-shadow:4px 4px 0px 0px rgba(0,0,0,1);padding:1.5rem;">
            <?php if (session()->has('errors')): ?>
            <?php foreach (session('errors') as $err): ?>
            <div style="background:#EF4444;color:#fff;border:4px solid #000;padding:.75rem;margin-bottom:.5rem;font-weight:700;font-size:.875rem;display:flex;align-items:center;gap:.5rem;"><span>✕</span> <?= $err ?></div>
            <?php endforeach; ?>
            <?php endif; ?>

            <form action="<?= base_url('register') ?>" method="POST">
                <div class="space-y-4">
                    <div>
                        <label style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;font-size:.875rem;display:block;margin-bottom:.25rem;">FULL NAME</label>
                        <input type="text" name="name" value="<?= old('name') ?>" required autofocus
                               style="width:100%;padding:.75rem 1rem;border:4px solid #000;background:#fff;font-weight:700;font-size:.875rem;"
                               onfocus="this.style.boxShadow='4px 4px 0px 0px rgba(0,0,0,1)';this.style.background='#FFDE4D';"
                               onblur="this.style.boxShadow='none';this.style.background='#fff';"
                               placeholder="John Doe" />
                    </div>
                    <div>
                        <label style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;font-size:.875rem;display:block;margin-bottom:.25rem;">EMAIL</label>
                        <input type="email" name="email" value="<?= old('email') ?>" required
                               style="width:100%;padding:.75rem 1rem;border:4px solid #000;background:#fff;font-weight:700;font-size:.875rem;"
                               onfocus="this.style.boxShadow='4px 4px 0px 0px rgba(0,0,0,1)';this.style.background='#FFDE4D';"
                               onblur="this.style.boxShadow='none';this.style.background='#fff';"
                               placeholder="your@email.com" />
                    </div>
                    <div>
                        <label style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;font-size:.875rem;display:block;margin-bottom:.25rem;">PASSWORD</label>
                        <input type="password" name="password" required
                               style="width:100%;padding:.75rem 1rem;border:4px solid #000;background:#fff;font-weight:700;font-size:.875rem;"
                               onfocus="this.style.boxShadow='4px 4px 0px 0px rgba(0,0,0,1)';this.style.background='#FFDE4D';"
                               onblur="this.style.boxShadow='none';this.style.background='#fff';"
                               placeholder="Min. 6 characters" />
                    </div>
                    <div>
                        <label style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;font-size:.875rem;display:block;margin-bottom:.25rem;">CONFIRM PASSWORD</label>
                        <input type="password" name="password_confirm" required
                               style="width:100%;padding:.75rem 1rem;border:4px solid #000;background:#fff;font-weight:700;font-size:.875rem;"
                               onfocus="this.style.boxShadow='4px 4px 0px 0px rgba(0,0,0,1)';this.style.background='#FFDE4D';"
                               onblur="this.style.boxShadow='none';this.style.background='#fff';"
                               placeholder="Repeat password" />
                    </div>
                    <button type="submit" style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;text-transform:uppercase;font-size:1rem;padding:.75rem 1.5rem;border:4px solid #000;box-shadow:4px 4px 0px 0px rgba(0,0,0,1);cursor:pointer;background:#F97316;color:#fff;width:100%;"
                            onmouseover="this.style.boxShadow='2px 2px 0px 0px #000';this.style.translate='2px 2px'"
                            onmouseout="this.style.boxShadow='4px 4px 0px 0px rgba(0,0,0,1)';this.style.translate='0'">CREATE ACCOUNT</button>
                </div>
            </form>

            <div style="border-top:4px solid #000;margin:1rem 0;"></div>
            <p style="text-align:center;font-size:.875rem;font-weight:700;">
                Already a member?
                <a href="<?= base_url('login') ?>" style="text-decoration:underline;padding:0 .25rem;">Sign in</a>
            </p>
        </div>

        <p style="text-align:center;margin-top:1rem;">
            <a href="<?= base_url('/products') ?>" style="color:#fff;font-weight:700;font-size:.875rem;text-decoration:underline;">← Back to Store</a>
        </p>
    </div>
</body>
</html>
