<?php
require_once 'config/db.php';
start_secure_session();

if (!is_admin()) {
    header("location: login.php");
    exit;
}

$leaders = [];
$leader_query = "SELECT id, full_name, party_affiliation, current_position, constituency FROM leaders ORDER BY full_name ASC";
$result = $conn->query($leader_query);
if ($result) {
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
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">KYL Admin Panel</a>
            <div class="d-flex">
                <span class="navbar-text me-3 text-white">Welcome, Admin</span>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid my-4">
        <h2 class="text-center mb-4 text-danger">Admin Management Dashboard</h2>
        
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="leaders-tab" data-bs-toggle="tab" data-bs-target="#leaders-content" type="button" role="tab">Manage Leaders (CRUD)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="citizens-tab" data-bs-toggle="tab" data-bs-target="#citizens-content" type="button" role="tab">View Citizens</button>
            </li>
        </ul>

        <div class="tab-content py-3" id="adminTabsContent">
            <div class="tab-pane fade show active" id="leaders-content" role="tabpanel">
                <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#leaderModal" data-mode="add">
                    <i class="fas fa-plus"></i> Add New Leader
                </button>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Name</th>
                                <th>Party</th>
                                <th>Position</th>
                                <th>Constituency</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="leader-table-body">
                            <?php if (empty($leaders)): ?>
                                <tr><td colspan="5" class="text-center text-muted">No leaders found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($leaders as $leader): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leader['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leader['party_affiliation']); ?></td>
                                        <td><?php echo htmlspecialchars($leader['current_position']); ?></td>
                                        <td><?php echo htmlspecialchars($leader['constituency']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning edit-leader-btn" data-id="<?php echo $leader['id']; ?>" data-bs-toggle="modal" data-bs-target="#leaderModal">Edit</button>
                                            <button class="btn btn-sm btn-danger delete-leader-btn" data-id="<?php echo $leader['id']; ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="citizens-content" role="tabpanel">
                <div class="card p-3 mb-3">
                    <input type="text" id="citizen-search-input" class="form-control" placeholder="Search citizens by name or email...">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Registered On</th>
                            </tr>
                        </thead>
                        <tbody id="citizen-table-body">
                            <tr><td colspan="4" class="text-center text-muted">Loading citizen data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'modals/leader_crud_modal.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin_panel.js"></script>
</body>
</html>