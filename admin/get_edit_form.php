<?php
session_start();
include '../includes/db_connect.php';

$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? 0;

try {
    $table = ($type === 'employer') ? 'employer_enquiries' : 'job_seeker_enquiries';
    $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    if ($data) {
        if ($type === 'employer') {
            echo '<form id="editForm" class="space-y-4">';
            echo '<input type="hidden" name="type" value="employer">';
            echo '<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>';
            echo '<input type="text" name="name" value="' . htmlspecialchars($data['name'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Organization</label>';
            echo '<input type="text" name="organisation_name" value="' . htmlspecialchars($data['organisation_name'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Position</label>';
            echo '<input type="text" name="position" value="' . htmlspecialchars($data['position'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Hiring Count</label>';
            echo '<input type="number" name="hiring_count" value="' . htmlspecialchars($data['hiring_count'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>';
            echo '<input type="email" name="email" value="' . htmlspecialchars($data['email'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phone</label>';
            echo '<input type="tel" name="phone" value="' . htmlspecialchars($data['phone'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div class="pt-4">';
            echo '<button type="button" onclick="saveEdit()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save Changes</button>';
            echo '</div>';
            echo '</form>';
        } else {
            echo '<form id="editForm" class="space-y-4">';
            echo '<input type="hidden" name="type" value="jobSeeker">';
            echo '<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>';
            echo '<input type="text" name="name" value="' . htmlspecialchars($data['name'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Position</label>';
            echo '<input type="text" name="position" value="' . htmlspecialchars($data['position'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Experience</label>';
            echo '<select name="fresher_experienced" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '<option value="Fresher" ' . (($data['fresher_experienced'] ?? '') === 'Fresher' ? 'selected' : '') . '>Fresher</option>';
            echo '<option value="Experienced" ' . (($data['fresher_experienced'] ?? '') === 'Experienced' ? 'selected' : '') . '>Experienced</option>';
            echo '</select>';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Experience Years</label>';
            echo '<input type="number" name="experience_years" value="' . htmlspecialchars($data['experience_years'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>';
            echo '<input type="email" name="email" value="' . htmlspecialchars($data['email'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div>';
            echo '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phone</label>';
            echo '<input type="tel" name="phone" value="' . htmlspecialchars($data['phone'] ?? '') . '" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700">';
            echo '</div>';
            
            echo '<div class="pt-4">';
            echo '<button type="button" onclick="saveEdit()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save Changes</button>';
            echo '</div>';
            echo '</form>';
        }
    } else {
        echo '<p class="text-red-500">Error: Record not found</p>';
    }
} catch (PDOException $e) {
    echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>