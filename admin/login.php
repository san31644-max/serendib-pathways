<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admin_users WHERE username = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <title>Admin Login - Serendib Pathways</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" 
    rel="stylesheet" 
  />
  <style>
    body {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .login-bg {
      background: url('https://scontent.fcmb4-2.fna.fbcdn.net/v/t39.30808-6/480961030_122115283394666022_8804538850921445524_n.jpg?_nc_cat=105&ccb=1-7&_nc_sid=f727a1&_nc_eui2=AeFs8wmodwiLejVDIQ_IG6Hq66NqeRDzMyTro2p5EPMzJByAudn7JCVbsjIosw4nmzliQQoEKhsT4pQwAMZrRwmL&_nc_ohc=uV5Y3qQmdvUQ7kNvwF6hCRJ&_nc_oc=AdkOozZAJjFquJ0RKtWlXY3Wpb7_xPJH4T_YtUnXDO2pjyTZfeSXsS09Lev8zzDj52PegsRyrMQIA_O4RDMgycVc&_nc_zt=23&_nc_ht=scontent.fcmb4-2.fna&_nc_gid=JUDURfmOnIx_dP6NsXjM-w&oh=00_AfSVmJRXQ2HlGnvndFJv6ibF52csTbTQnzHZ_JpcrMGcmQ&oe=68880073') center center / cover no-repeat;
      position: relative;
      width: 100%;
      height: 100%;
      background-image: linear-gradient(180deg,rgba(15,33,27,.05),rgba(15,33,27,.5)), url('../assets/about-6.jpg');
    }
    .glass-effect {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .input-group {
      position: relative;
    }
    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #6b7280;
      cursor: pointer;
      z-index: 10;
      transition: color 0.2s ease;
    }
    .toggle-password:hover {
      color: #059669;
    }
    .form-input {
      padding-left: 24px;
      transition: all 0.3s ease;
    }
    .form-input:focus {
      transform: translateY(-1px);
      box-shadow: 0 10px 25px rgba(34, 197, 94, 0.1);
    }
    .login-card {
      animation: slideInUp 0.6s ease-out;
    }
    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .logo-container {
      animation: fadeInScale 0.8s ease-out;
    }
    @keyframes fadeInScale {
      from {
        opacity: 0;
        transform: scale(0.8);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }
    .btn-primary {
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(34, 197, 94, 0.3);
    }
    @media (max-width: 900px) {
      .pc-panel {
        display: none;
      }
      .mobile-panel {
        display: flex;
      }
    }
    @media (min-width: 900px) {
      .pc-panel {
        display: flex;
      }
      .mobile-panel {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="flex items-center justify-center w-full h-screen min-h-screen">
    <div class="w-full h-full max-w-full bg-white rounded-none shadow-2xl overflow-hidden flex flex-col md:flex-row">
      
      <!-- Left side: Background image -->
      <div class="pc-panel w-full md:w-1/2 login-bg"></div>
      
      <!-- Right panel: Login Form -->
      <div class="w-full md:w-1/2 flex items-center justify-center bg-gradient-to-br from-slate-50 to-white relative">
        <div class="w-full px-6 md:px-12 py-8 flex flex-col justify-center h-full">
          
          <!-- Mobile Logo -->
          <div class="mobile-panel flex flex-col items-center mb-0">
            <div class="logo-container flex items-center justify-center mb-0">
              <img src="../assets/serendib-pathways-logo.png" alt="Serendib Pathways" class="h-28 w-28 object-contain rounded-2xl">
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-green-700 mb-1">Welcome Back</h1>
            <p class="text-green-600 text-sm">Sign in to your admin account</p>
          </div>
         
          <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 text-sm text-center flex items-center justify-center">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>
          
          <form method="POST" action="" class="space-y-6 flex justify-center w-full">
            <div class="w-full max-w-md login-card">
              
              <!-- Username Field -->
              <div class="input-group mb-6">
                <label for="username" class="block text-sm font-semibold text-green-700 mb-3">Username</label>
                <div class="relative">
                  <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    autocomplete="username"
                    placeholder="    Enter your username"
                    class="form-input w-full px-4 py-4 border-2 border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition text-base bg-white shadow-sm hover:border-green-300"
                  />
                </div>
              </div>
              
              <!-- Password Field -->
              <div class="input-group mb-6">
                <label for="password" class="block text-sm font-semibold text-green-700 mb-3">Password</label>
                <div class="relative">
                  <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="    Enter your password"
                    class="form-input w-full px-4 py-4 pr-12 border-2 border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition text-base bg-white shadow-sm hover:border-green-300"
                  />
                  <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
              </div>
              
              <!-- Login Button -->
              <button
                type="submit"
                class="btn-primary w-full text-white py-4 rounded-xl font-bold transition duration-300 shadow-lg hover:shadow-xl flex items-center justify-center"
              >
                Sign In
              </button>
              
              <!-- Additional Info -->
              <div class="mt-6 text-center">
                <p class="text-xs text-green-600">
                  Secure admin access
                </p>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Password visibility toggle
    document.addEventListener('DOMContentLoaded', function() {
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      
      if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
          const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
          passwordInput.setAttribute('type', type);
          if (type === 'password') {
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
          } else {
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
          }
        });
      }
    });
  </script>
</body>
</html>
