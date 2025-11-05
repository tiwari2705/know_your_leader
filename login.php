<?php
require_once 'config/db.php';
start_secure_session();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: " . (is_admin() ? 'admin_dashboard.php' : 'citizen_dashboard.php'));
    exit;
}

$login_error = '';
$register_success = '';

if (isset($_GET['register_success']) && $_GET['register_success'] == '1') {
    $register_success = "Registration successful! You can now log in.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['form_type']) && $_POST['form_type'] === 'login') {
        $user_type = $_POST['user_type'] ?? 'citizen';
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $table = ($user_type === 'admin') ? 'admin' : 'users';
        $redirect_url = ($user_type === 'admin') ? 'admin_dashboard.php' : 'citizen_dashboard.php';
        $field = ($user_type === 'admin') ? 'username' : 'email';

        $sql = "SELECT id, full_name, password FROM $table WHERE $field = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $full_name_or_username, $hashed_password);
                    if ($stmt->fetch()) {
                        $login_successful = false;

                        if ($user_type === 'citizen' && password_verify($password, $hashed_password)) {
                            $login_successful = true;
                        } elseif ($user_type === 'admin' && $password === $hashed_password) {
                            $login_successful = true;
                        }

                        if ($login_successful) {
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["user_type"] = $user_type;
                            $_SESSION["username"] = $username; 
                            $_SESSION["full_name"] = ($user_type === 'citizen') ? $full_name_or_username : 'Admin';

                            header("location: $redirect_url");
                            exit;
                        } else {
                            $login_error = "Invalid credentials.";
                        }
                    }
                } else {
                    $login_error = "No account found with that email/username.";
                }
            } else {
                $login_error = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - Know Your Leader</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

    <nav class="bg-white shadow-sm">
  <div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      
      <a href="index.html" class="text-xl font-bold text-[#2c3e50]">
        <span class="text-[#1abc9c]">KNOW</span> YOUR LEADER
      </a>

      <div class="hidden md:flex items-center space-x-6">
        <a href="index.html" class="text-gray-600 hover:text-blue-600">Home</a>
        <a href="login.php" class="text-blue-600 font-medium hover:text-blue-700">Login</a>
        <a href="register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Register</a>
      </div>
      
    </div>
  </div>
</nav>

    <div class="relative min-h-[calc(100vh-64px)] flex items-center justify-center p-4">

      <div class="absolute inset-0 z-[-1]">
        <img src="./assets/back.png" class="w-full h-full object-cover" alt="Background">
        <div class="absolute inset-0 bg-black/30"></div> </div>
      
      <div class="w-full max-w-[950px] bg-white [box-shadow:0_2px_10px_-3px_rgba(14,14,14,0.3)] rounded-2xl overflow-hidden">
        <div class="flex items-center max-md:flex-col w-full gap-y-4">
          
          <div class="md:max-w-[570px] w-full h-full">
            <div class="md:aspect-[7/10] bg-gray-50 relative before:absolute before:inset-0 before:bg-black/40 overflow-hidden w-full h-full">
              <img src="./assets/login1.png" class="w-full h-full object-cover" alt="login img" />
              <div class="absolute inset-0 flex items-end justify-center">
                <div class="w-full bg-gradient-to-t from-black/50 via-black/50 to-transparent absolute bottom-0 p-6 max-md:hidden">
                  <h1 class="text-white text-2xl font-semibold">Welcome Back</h1>
                  <p class="text-slate-300 text-[15px] font-medium mt-3 leading-relaxed">
                    Access leader profiles, performance data, and make informed decisions.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="w-full h-full px-8 lg:px-20 py-8 max-md:-order-1">
            <div class="md:max-w-md w-full mx-auto">
              <div class="mb-8">
                <h3 class="text-4xl font-bold text-slate-900">Sign in</h3>
              </div>

              <?php if ($login_error): ?>
                <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                  <?php echo htmlspecialchars($login_error); ?>
                </div>
              <?php endif; ?>
              <?php if ($register_success): ?>
                <div class="mb-4 p-3 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                  <?php echo htmlspecialchars($register_success); ?>
                </div>
              <?php endif; ?>

              <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" role="tablist">
                  <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="citizen-tab" type="button" role="tab" aria-controls="citizen-login" aria-selected="true">Citizen</button>
                  </li>
                  <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="admin-tab" type="button" role="tab" aria-controls="admin-login" aria-selected="false">Admin</button>
                  </li>
                </ul>
              </div>

              <div id="myTabContent">
                
                <div class="" id="citizen-login" role="tabpanel" aria-labelledby="citizen-tab">
                  <form action="login.php" method="POST" class="w-full">
                    <input type="hidden" name="form_type" value="login">
                    <input type="hidden" name="user_type" value="citizen">
                    
                    <div>
                      <div class="relative flex items-center">
                        <input name="username" type="email" required class="w-full text-sm border-b border-gray-300 focus:border-black pr-8 px-2 py-3 outline-none" placeholder="Enter email" />
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-[18px] h-[18px] absolute right-2" viewBox="0 0 682.667 682.667"><g clip-path="url(#a)" transform="matrix(1.33 0 0 -1.33 0 682.667)"><path fill="none" stroke-miterlimit="10" stroke-width="40" d="M452 444H60c-22.091 0-40-17.909-40-40v-39.446l212.127-157.782c14.17-10.54 33.576-10.54 47.746 0L492 364.554V404c0 22.091-17.909 40-40 40Z"></path><path d="M472 274.9V107.999c0-11.027-8.972-20-20-20H60c-11.028 0-20 8.973-20 20V274.9L0 304.652V107.999c0-33.084 26.916-60 60-60h392c33.084 0 60 26.916 60 60v196.653Z"></path></g></svg>
                      </div>
                    </div>
                    <div class="mt-8">
                      <div class="relative flex items-center">
                        <input name="password" type="password" required class="w-full text-sm border-b border-gray-300 focus:border-black pr-8 px-2 py-3 outline-none" placeholder="Enter password" />
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-[18px] h-[18px] absolute right-2 cursor-pointer" viewBox="0 0 128 128"><path d="M64 104C22.127 104 1.367 67.496.504 65.943a4 4 0 0 1 0-3.887C1.367 60.504 22.127 24 64 24s62.633 36.504 63.496 38.057a4 4 0 0 1 0 3.887C126.633 67.496 105.873 104 64 104zM8.707 63.994C13.465 71.205 32.146 96 64 96c31.955 0 50.553-24.775 55.293-31.994C114.535 56.795 95.854 32 64 32 32.045 32 13.447 56.775 8.707 63.994zM64 88c-13.234 0-24-10.766-24-24s10.766-24 24-24 24 10.766 24 24-10.766 24-24 24zm0-40c-8.822 0-16 7.178-16 16s7.178 16 16 16 16-7.178 16-16-7.178-16-16-16z"></path></svg>
                      </div>
                    </div>
                    <div class="mt-12">
                      <button type="submit" class="w-full shadow-xl py-2 px-4 text-[15px] font-medium tracking-wide rounded-md cursor-pointer text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                        Sign in as Citizen
                      </button>
                    </div>
                  </form>
                </div>

                <div class="hidden" id="admin-login" role="tabpanel" aria-labelledby="admin-tab">
                  <form action="login.php" method="POST" class="w-full">
                    <input type="hidden" name="form_type" value="login">
                    <input type="hidden" name="user_type" value="admin">
                    
                    <div>
                      <div class="relative flex items-center">
                        <input name="username" type="text" required class="w-full text-sm border-b border-gray-300 focus:border-black pr-8 px-2 py-3 outline-none" placeholder="Enter admin username" />
                      </div>
                    </div>
                    <div class="mt-8">
                      <div class="relative flex items-center">
                        <input name="password" type="password" required class="w-full text-sm border-b border-gray-300 focus:border-black pr-8 px-2 py-3 outline-none" placeholder="Enter admin password" />
                      </div>
                    </div>
                    <div class="mt-12">
                      <button type="submit" class="w-full shadow-xl py-2 px-4 text-[15px] font-medium tracking-wide rounded-md cursor-pointer text-white bg-orange-600 hover:bg-orange-700 focus:outline-none">
                        Sign in as Admin
                      </button>
                    </div>
                  </form>
                </div>
              </div> 

              <div class="flex flex-wrap items-center justify-between gap-4 mt-6">
                <div>
                  <!-- <a href="forgot_password.php" class="text-blue-600 font-medium text-sm hover:underline">
                    Forgot Password?
                  </a> -->
                </div>
                <div>
                  <a href="index.html" class="text-gray-600 font-medium text-sm hover:underline">
                    Back to Home
                  </a>
                </div>
              </div>

              <p class="text-slate-600 text-sm text-center mt-8">Don't have an account? <a href="register.php" class="text-blue-600 font-medium hover:underline ml-1 whitespace-nowrap">Register here</a></p>

            </div>
          </div>
        </div>
      </div>
    </div>

<script>
  const citizenTab = document.getElementById('citizen-tab');
  const adminTab = document.getElementById('admin-tab');
  const citizenForm = document.getElementById('citizen-login');
  const adminForm = document.getElementById('admin-login');
  
  const activeClasses = 'border-blue-600 text-blue-600';
  const inactiveClasses = 'border-transparent hover:text-gray-600 hover:border-gray-300';

  citizenTab.addEventListener('click', () => {
    citizenForm.classList.remove('hidden');
    adminForm.classList.add('hidden');
    
    citizenTab.classList.add(...activeClasses.split(' '));
    citizenTab.classList.remove(...inactiveClasses.split(' '));
    citizenTab.setAttribute('aria-selected', 'true');

    adminTab.classList.add(...inactiveClasses.split(' '));
    adminTab.classList.remove(...activeClasses.split(' '));
    adminTab.setAttribute('aria-selected', 'false');
  });

  adminTab.addEventListener('click', () => {
    adminForm.classList.remove('hidden');
    citizenForm.classList.add('hidden');

    adminTab.classList.add(...activeClasses.split(' '));
    adminTab.classList.remove(...inactiveClasses.split(' '));
    adminTab.setAttribute('aria-selected', 'true');

    citizenTab.classList.add(...inactiveClasses.split(' '));
    citizenTab.classList.remove(...activeClasses.split(' '));
    citizenTab.setAttribute('aria-selected', 'false');
  });

  <?php if (isset($_POST['user_type']) && $_POST['user_type'] === 'admin'): ?>
    adminTab.click();
  <?php else: ?>
    citizenTab.click();
  <?php endif; ?>
</script>

</body>
</html>