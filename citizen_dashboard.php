<?php
require_once 'config/db.php';
start_secure_session();

if (!is_citizen()) {
    header("location: login.php");
    exit;
}

function renderStars($rating, $size = 'sm') {
    $starsHtml = '';
    $roundedRating = floor($rating * 2) / 2; 
    $starClass = $size === 'lg' ? 'fs-4' : ''; 

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $roundedRating) {
            $starsHtml .= '<i class="fas fa-star text-warning ' . $starClass . '"></i>'; 
        } else if ($i - 0.5 === $roundedRating) {
            $starsHtml .= '<i class="fas fa-star-half-alt text-warning ' . $starClass . '"></i>';
        } else {
            $starsHtml .= '<i class="far fa-star text-warning ' . $starClass . '"></i>'; 
        }
    }
    return $starsHtml;
}
$leaders = [];
$sql = "SELECT 
            l.id, l.full_name, l.current_position, l.party_affiliation, l.photo_path,
            COALESCE(AVG(r.rating), 0) AS average_rating,
            COUNT(r.id) AS total_ratings
        FROM leaders l
        LEFT JOIN leader_reviews r ON l.id = r.leader_id
        GROUP BY l.id
        ORDER BY l.full_name ASC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leaders[] = $row;
    }
}
$conn->close(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citizen Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/chatbot_style.css"> 

    </head>
<body>

<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <span class="ky-brand"> </span> KNOW YOUR LEADER
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item">
                    <span class="navbar-text me-3">
                        <i class="fas fa-user-circle me-1"></i>
                        Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<header class="search-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 text-center text-lg-start">
                <h1 class="hero-title">
                    <i class="fas fa-landmark"></i> 
                    KNOW YOUR LEADER
                </h1>
                <p class="hero-subtitle">Use the filters to find and review leader profiles.</p>
            </div>
            
            <div class="col-lg-7">
                <form id="leader-search-form" class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" placeholder="Name...">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="party" class="form-control" placeholder="Party Affiliation...">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="position" class="form-control" placeholder="Current Position...">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="constituency" class="form-control" placeholder="Constituency...">
                    </div>
                    <div class="col-12 text-end mt-3">
                        <button type="reset" class="btn btn-secondary-outline me-2">Clear Filters</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</header>

<section id="news-hero-section" class="container text-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2 text-muted">Fetching the latest political trends...</p>
</section>
<div class="container py-4">
    <div class="row" id="leader-results">
        <?php if (empty($leaders)): ?>
            <div class="col-12 text-center text-muted p-5">
                No leaders have been added to the database yet.
            </div>
        <?php else: ?>
            <?php foreach ($leaders as $leader): ?>
                
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm leader-card h-100">
                        
                        <?php
                        // Use default image if photo_path is empty or null
                        $image_path = !empty($leader['photo_path']) ? htmlspecialchars($leader['photo_path']) : 'assets/default_leader.png';
                        ?>

                        <img src="<?php echo $image_path; ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($leader['full_name']); ?>">
                            
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-primary"><?php echo htmlspecialchars($leader['full_name']); ?></h5>
                            <p class="card-text flex-grow-1">
                                <strong>Position:</strong> <?php echo htmlspecialchars($leader['current_position'] ?: 'N/A'); ?><br>
                                <strong>Party:</strong> <?php echo htmlspecialchars($leader['party_affiliation'] ?: 'N/A'); ?><br>
                            </p>
                            
                            <div class="mb-2">
                                <?php echo renderStars($leader['average_rating']); ?>
                                <span class="ms-1 text-muted small">(<?php echo $leader['total_ratings']; ?>)</span>
                            </div>
                            <button class="btn btn-sm btn-outline-primary view-details-btn mt-auto" 
                                    data-id="<?php echo $leader['id']; ?>">
                                View Full Profile
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'modals/leader_profile_modal.php'; ?>

<?php include 'includes/chatbot_widget.php'; ?>


<footer class="bg-dark text-light py-3 mt-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="mb-2 mb-md-0">
            &copy; <?php echo date('Y'); ?> Know Your Leader
        </div>
        <div>
            <a href="https://www.linkedin.com/in/harsh-tiwari2705/" target="_blank" rel="noopener noreferrer" class="text-light me-3" aria-label="LinkedIn">
                <i class="fab fa-linkedin fa-lg"></i>
            </a>
            <a href="https://www.instagram.com/h_tiwari027/" target="_blank" rel="noopener noreferrer" class="text-light" aria-label="Instagram">
                <i class="fab fa-instagram fa-lg"></i>
            </a>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/citizen_dashboard.js"></script>

<script src="js/chatbot_logic.js"></script>

</body>
</html>