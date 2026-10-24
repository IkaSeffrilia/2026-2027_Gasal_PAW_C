<?php
function validateName(&$errors, $field_list, $field_name) {
    $pattern = '/^[a-zA-Z-]{2,}$/';

    // ✅ Baris 4 seharusnya begini: pakai $field_name, bukan $field_list
    if (!isset($field_list[$field_name]) || trim($field_list[$field_name]) === '') {
        $errors[$field_name] = 'tidak boleh kosong';
    } elseif (!preg_match($pattern, $field_list[$field_name])) {
        $errors[$field_name] = 'hanya boleh huruf dan tanda hubung (min 2 karakter)';
    }
}
?>