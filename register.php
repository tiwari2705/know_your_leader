<?php
require_once 'config/db.php';
start_secure_session();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: " . (is_admin() ? 'admin_dashboard.php' : 'citizen_dashboard.php'));
    exit;
}

$register_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['form_type']) && $_POST['form_type'] === 'register') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
            $register_error = "All fields are required.";
        } elseif ($password !== $confirm_password) {
            $register_error = "Passwords do not match.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $register_error = "Invalid email format.";
        } else {
            $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt->bind_param("sss", $full_name, $email, $hashed_password);

                if ($stmt->execute()) {
                    header("location: login.php?register_success=1");
                    exit;
                } else {
                    if ($conn->errno == 1062) {
                        $register_error = "This email is already registered.";
                    } else {
                        $register_error = "Something went wrong. Please try again. Error: " . $stmt->error;
                    }
                }
                $stmt->close();
            }
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
    <title>Register - Know Your Leader</title>
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
              <img src="./assets/login1.png" class="w-full h-full object-cover" alt="register img" />
              <div class="absolute inset-0 flex items-end justify-center">
                <div class="w-full bg-gradient-to-t from-black/50 via-black/50 to-transparent absolute bottom-0 p-6 max-md:hidden">
                  <h1 class="text-white text-2xl font-semibold">Join the Conversation</h1>
                  <p class="text-slate-300 text-[15px] font-medium mt-3 leading-relaxed">
                    Create an account to follow leaders, join discussions, and stay informed.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="w-full h-full px-8 lg:px-20 py-8 max-md:-order-1">
            <form class="md:max-w-md w-full mx-auto" action="register.php" method="POST">
              <input type="hidden" name="form_type" value="register">

              <div class="mb-8">
                <h3 class="text-4xl font-bold text-slate-900">Create Account</h3>
              </div>

              <?php if ($register_error): ?>
                <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                  <?php echo htmlspecialchars($register_error); ?>
                </div>
              <?php endif; ?>

              <div>
                <div class="relative flex items-center">
                  <input name="full_name" type="text" required class="w-full text-sm border-b border-gray-300 focus:border-black px-2 py-3 outline-none" placeholder="Enter full name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>
              </div>
              <div class="mt-8">
                <div class="relative flex items-center">
                  <input name="email" type="email" required class="w-full text-sm border-b border-gray-300 focus:border-black px-2 py-3 outline-none" placeholder="Enter email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
              </div>
              <div class="mt-8">
                <div class="relative flex items-center">
                  <input name="password" type="password" required class="w-full text-sm border-b border-gray-300 focus:border-black px-2 py-3 outline-none" placeholder="Enter password" />
                </div>
              </div>
              <div class="mt-8">
                <div class="relative flex items-center">
                  <input name="confirm_password" type="password" required class="w-full text-sm border-b border-gray-300 focus:border-black px-2 py-3 outline-none" placeholder="Confirm password" />
                </div>
              </div>

              <div class="mt-12">
                <button type="submit" class="w-full shadow-xl py-2 px-4 text-[1F5px] font-medium tracking-wide rounded-md cursor-pointer text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                  Register
                </button>
                <p class="text-slate-600 text-sm text-center mt-6">Already have an account? <a href="login.php" class="text-blue-600 font-medium hover:underline ml-1 whitespace-nowrap">Sign in here</a></p>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>

</body>
</html>