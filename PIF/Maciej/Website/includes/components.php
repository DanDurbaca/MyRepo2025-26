<?php
// includes/components.php - Reusable UI components for tables, cards, and form groups

// Function to render an HTML table dynamically
function renderTable($data, $columns, $actions = []) {
    // If there is no data, return a placeholder message
    if (empty($data)) {
        return '<p class="text-center text-muted">No data available.</p>';
    }
    
    // Start table HTML structure
    $html = '<div class="table-container"><table class="table"><thead><tr>';
    
    // Render table headers based on the columns array
    foreach ($columns as $key => $label) {
        $html .= '<th>' . htmlspecialchars($label) . '</th>'; // Escape HTML to prevent XSS
    }
    
    // Add an "Actions" column if any actions are specified
    if (!empty($actions)) {
        $html .= '<th>Actions</th>';
    }
    
    $html .= '</tr></thead><tbody>'; // Close header, start body

    // Loop through each row of data
    foreach ($data as $row) {
        $html .= '<tr>'; // Start table row
        
        // Loop through each column and output the corresponding value
        foreach ($columns as $key => $label) {
            $value = $row[$key] ?? ''; // Fallback to empty string if key not found
            $html .= '<td>' . htmlspecialchars($value) . '</td>'; // Table cell
        }
        
        // Render action buttons if specified
        if (!empty($actions)) {
            $html .= '<td class="action-buttons">'; // Cell for actions
            foreach ($actions as $action => $config) {
                // Replace ":id" placeholder in URL with the row's ID
                $url = str_replace(':id', $row['id'] ?? '', $config['url'] ?? '');
                $class = $config['class'] ?? 'btn btn-sm'; // Default CSS class
                $icon = $config['icon'] ?? '';             // Optional icon class
                $text = $config['text'] ?? ucfirst($action); // Button text
                
                // Render anchor link as a button
                $html .= '<a href="' . $url . '" class="' . $class . '">';
                if ($icon) $html .= '<i class="' . $icon . '"></i> '; // Optional icon
                $html .= $text . '</a> ';
            }
            $html .= '</td>'; // Close actions cell
        }
        
        $html .= '</tr>'; // Close table row
    }
    
    $html .= '</tbody></table></div>'; // Close table and container
    return $html; // Return generated HTML
}

// Function to render a card component
function renderCard($title, $content, $footer = '', $badge = '') {
    $html = '<div class="card">'; // Card container
    
    // Header with optional title and badge
    if ($title || $badge) {
        $html .= '<div class="card-header">';
        if ($title) $html .= '<h3 class="card-title">' . htmlspecialchars($title) . '</h3>';
        if ($badge) $html .= '<span class="badge">' . htmlspecialchars($badge) . '</span>';
        $html .= '</div>';
    }
    
    // Main card content
    $html .= '<div class="card-content">' . $content . '</div>';
    
    // Optional footer section
    if ($footer) {
        $html .= '<div class="card-footer">' . $footer . '</div>';
    }
    
    $html .= '</div>'; // Close card container
    return $html; // Return HTML
}

// Function to render a form group (label + input/select/textarea)
function renderFormGroup($name, $label, $type = 'text', $value = '', $options = []) {
    $html = '<div class="form-group">'; // Form group wrapper
    $html .= '<label for="' . $name . '">' . htmlspecialchars($label) . '</label>'; // Label
    
    if ($type === 'select') {
        // Render select dropdown
        $html .= '<select id="' . $name . '" name="' . $name . '" class="form-control">';
        foreach ($options as $key => $val) {
            $selected = ($value == $key) ? ' selected' : ''; // Preselect option
            $html .= '<option value="' . htmlspecialchars($key) . '"' . $selected . '>';
            $html .= htmlspecialchars($val) . '</option>';
        }
        $html .= '</select>';
    } elseif ($type === 'textarea') {
        // Render textarea
        $html .= '<textarea id="' . $name . '" name="' . $name . '" class="form-control">';
        $html .= htmlspecialchars($value) . '</textarea>';
    } else {
        // Render standard input (text, number, password, etc.)
        $html .= '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" ';
        $html .= 'value="' . htmlspecialchars($value) . '" class="form-control">';
    }
    
    $html .= '</div>'; // Close form group
    return $html; // Return HTML
}
?>