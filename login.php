<?php
session_start();
$loginSuccess = isset($_SESSION['login_success']) ? $_SESSION['login_success'] : false;
$loginError = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';

// Clear session messages after retrieving them
unset($_SESSION['login_success']);
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BudgetTrack | Department Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="public/css/login.css">
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script crossorigin src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const year = document.getElementById('year');
            if (year) year.textContent = new Date().getFullYear();
            
            // Handle login success and error messages
            <?php if ($loginSuccess): ?>
                // Show success message and redirect after 2 seconds
                setTimeout(() => {
                    alert('Login successful! Redirecting to dashboard...');
                }, 100);
            <?php elseif ($loginError): ?>
                // Show error message
                setTimeout(() => {
                    alert('<?php echo addslashes($loginError); ?>');
                }, 100);
            <?php endif; ?>
        });
    </script>
    </head>
<body class="min-h-screen bg-fixed bg-cover bg-center login-bg">
    <div id="root" class="min-h-screen grid place-items-center px-4 py-8"></div>

    <script type="text/babel">
        function LoginCard() {
            return (
                <div className="relative grid w-full max-w-[1100px] grid-cols-1 md:grid-cols-[1.2fr_1fr] overflow-hidden border border-slate-200 bg-white shadow-xl">
                    {/* Left panel: full image, squared edge */}
                    <div className="relative hidden md:block bg-center bg-cover left-panel-bg">
                        {/* University branding overlay */}
                        <div className="absolute inset-x-0 top-0 flex items-center gap-3 bg-black/25 backdrop-blur-[5px] px-6 py-4">
                            <img src="img/evsu_logo.png" alt="EVSU" className="h-10 w-10 object-contain drop-shadow" />
                            <div className="text-white leading-tight drop-shadow">
                                <div className="font-extrabold tracking-wider text-[26px] md:text-[28px]">EASTERN VISAYAS</div>
                                <div className="mt-1 h-1 bg-[#ffd54a] evsu-underline"></div>
                                <div className="font-semibold tracking-[.35em] text-xs md:text-sm opacity-95">STATE UNIVERSITY</div>
                            </div>
                        </div>
                        {/* Curved divider overlay */}
                        <svg className="absolute -right-px top-0 h-full w-24" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                            <path d="M100,100 L100,0 C108,40 0,65 80,100 L0,100 Z" fill="#ffffff" />
                        </svg>
                    </div>
                    {/* Right panel: squared, red heading */}
                    <div className="p-10">
                        <h1 className="mb-6 text-[56px] leading-none text-[#b30000] sign-in-title">SIGN IN PORTAL</h1>
                        <p className="text-slate-500 mb-6">Authorized department heads and office staff only</p>
                        <form className="space-y-4" method="post" action="../capstone/auth/login.php">
                            <div>
                                <label className="block font-semibold mb-1">Email</label>
                                <input name="email" type="email" placeholder="example@evsu.edu.ph" className="w-full rounded-none border border-slate-300 px-4 py-3 outline-none focus:border-[#b30000] focus:ring-2 focus:ring-[#b30000]/20" required />
                            </div>
                            <div>
                                <label className="block font-semibold mb-1">Password</label>
                                <input name="password" type="password" placeholder="Password" className="w-full rounded-none border border-slate-300 px-4 py-3 outline-none focus:border-[#b30000] focus:ring-2 focus:ring-[#b30000]/20" required />
                            </div>
                            <button className="w-full rounded-none bg-[#cc0000] py-3 font-bold text-white hover:bg-[#b30000]">Login</button>
                        </form>
                        <div className="flex items-center justify-between text-sm text-slate-600 mt-3">
                            <a className="text-[#b30000] font-semibold" href="forgot.html">Forgot Password?</a>
                        </div>
                        <p className="text-sm text-slate-500 mt-6">© <span id="year"></span> EVSU–Ormoc Campus • BudgetTrack</p>
                        {/* Back link aligned under the form */}
                        <div className="pt-6">
                            <a href="index.php" className="text-[#b30000] font-semibold hover:underline">← Back to Homepage</a>
                        </div>
                    </div>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<LoginCard />);
    </script>
</body>
</html>
