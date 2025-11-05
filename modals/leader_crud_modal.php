<div class="modal fade" id="leaderModal" tabindex="-1" aria-labelledby="leaderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="leaderModalLabel">Add New Leader</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="leader-form" enctype="multipart/form-data">
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="leader_id" id="leader_id">

                    <h6 class="text-primary mt-2 border-bottom pb-1">Basic Information</h6>
                    <div class="row mb-3 g-3">
                        <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Photo (Upload)</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
                        <div class="col-md-4"><label class="form-label">Age</label><input type="number" name="age" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Gender</label><select name="gender" class="form-select"><option value="">Select...</option><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div>
                    </div>

                    <h6 class="text-primary mt-4 border-bottom pb-1">Political Information</h6>
                    <div class="row mb-3 g-3">
                        <div class="col-md-6"><label class="form-label">Current Position</label><input type="text" name="current_position" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Party Affiliation</label><input type="text" name="party_affiliation" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Constituency</label><input type="text" name="constituency" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Career Duration (Years)</label><input type="number" name="career_duration" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Past Positions</label><textarea name="past_positions" class="form-control" rows="2"></textarea></div>
                    </div>

                    <h6 class="text-primary mt-4 border-bottom pb-1">Financial & Property Info</h6>
                    <div class="row mb-3 g-3">
                        <div class="col-md-6"><label class="form-label">Declared Assets (Rs.)</label><input type="number" step="0.01" name="declared_assets" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Annual Income (Rs.)</label><input type="number" step="0.01" name="annual_income" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Businesses Owned</label><textarea name="businesses_owned" class="form-control" rows="2"></textarea></div>
                        <div class="col-12"><label class="form-label">Investments</label><textarea name="investments" class="form-control" rows="2"></textarea></div>
                    </div>

                    <h6 class="text-primary mt-4 border-bottom pb-1">Legal & Transparency</h6>
                    <div class="row mb-3 g-3">
                        <div class="col-md-4"><label class="form-label">Total Police Cases</label><input type="number" name="total_police_cases" class="form-control"></div>
                        <div class="col-md-8"><label class="form-label">Type of Cases</label><input type="text" name="case_types" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Court Case Status</label><textarea name="court_case_status" class="form-control" rows="2"></textarea></div>
                    </div>

                    <h6 class="text-primary mt-4 border-bottom pb-1">Education / Personal</h6>
                    <div class="row mb-3 g-3">
                        <div class="col-md-6"><label class="form-label">Number of Children</label><input type="number" name="num_children" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Education / Qualifications</label><input type="text" name="qualifications" class="form-control"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Leader Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>