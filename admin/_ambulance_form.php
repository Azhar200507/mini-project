<?php
// admin/_ambulance_form.php – Reusable ambulance form fields
// Used inside both Add and Edit modals
?>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-600">Driver Name <span class="text-danger">*</span></label>
        <input type="text" name="driver_name" class="form-control"
               placeholder="e.g. Rajan Mehta" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Phone Number <span class="text-danger">*</span></label>
        <input type="tel" name="phone" class="form-control"
               placeholder="e.g. 9001122334" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Vehicle Number <span class="text-danger">*</span></label>
        <input type="text" name="vehicle_no" class="form-control"
               placeholder="e.g. MH-01-AB-1234" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Area / Location <span class="text-danger">*</span></label>
        <input type="text" name="area" class="form-control"
               placeholder="e.g. Andheri West, Mumbai" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Latitude</label>
        <input type="number" name="latitude" class="form-control"
               step="0.00000001" placeholder="e.g. 19.13600000">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Longitude</label>
        <input type="number" name="longitude" class="form-control"
               step="0.00000001" placeholder="e.g. 72.82600000">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Status</label>
        <select name="status" class="form-select">
            <option value="available">Available</option>
            <option value="busy">Busy</option>
            <option value="offline">Offline</option>
        </select>
    </div>
</div>
