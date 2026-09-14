<?php
/**
 * Form controls, generated from the schema.
 *
 * Every screen's markup comes from here, so a new content field is one schema
 * entry and one template line rather than a new page of admin code.
 *
 * Naming: scalars post as f[dotted.path]; list rows post as
 * l[dotted.path][index][subfield], which PHP unpacks into nested arrays for
 * gns_apply_save() to walk.
 */

function gns_field_id($name)
{
    return 'fld-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
}

/** One field, including its label, help text and grid width. */
function gns_render_field(array $field, array $content)
{
    $path = $field['p'];
    $value = gns_get($content, $path, $field['t'] === 'list' ? array() : '');
    $full = ($field['t'] === 'list' || ($field['w'] ?? '') !== 'half');
    $name = 'f[' . $path . ']';
    $id = gns_field_id($path);

    echo '<div class="field' . ($full ? ' is-full' : '') . '">';

    if ($field['t'] === 'toggle') {
        echo '<span class="label">' . $field['l'] . '</span>';
        gns_render_control($field, $value, $name, $id);
    } elseif ($field['t'] === 'list') {
        echo '<span class="label">' . $field['l'] . '</span>';
        if (!empty($field['h'])) {
            echo '<p class="help">' . $field['h'] . '</p>';
        }
        gns_render_list($field, is_array($value) ? $value : array());
        echo '</div>';
        return;
    } else {
        echo '<label for="' . e($id) . '">' . $field['l'] . '</label>';
        gns_render_control($field, $value, $name, $id);
    }

    if (!empty($field['h'])) {
        echo '<p class="help">' . $field['h'] . '</p>';
    }
    echo '</div>';
}

/** Just the input, so list rows can reuse it with their own names. */
function gns_render_control(array $field, $value, $name, $id)
{
    $type = $field['t'];
    $attr = ' name="' . e($name) . '" id="' . e($id) . '"';

    switch ($type) {
        case 'textarea':
        case 'richtext':
            echo '<textarea' . $attr . ' rows="3">' . e($value) . '</textarea>';
            break;

        case 'code':
            echo '<textarea' . $attr . ' rows="12" class="is-code" spellcheck="false">' . e($value) . '</textarea>';
            break;

        case 'number':
            echo '<input type="number" step="1"' . $attr . ' value="' . e($value) . '">';
            break;

        case 'toggle':
            echo '<label class="toggle">'
               . '<input type="checkbox"' . $attr . ' value="1"' . (!empty($value) ? ' checked' : '') . '>'
               . '<span class="toggle-box" aria-hidden="true"></span>'
               . '<span class="toggle-text">' . (!empty($value) ? 'On' : 'Off') . '</span>'
               . '</label>';
            break;

        case 'select':
            echo '<select' . $attr . '>';
            foreach ($field['o'] as $key => $label) {
                $sel = ((string)$value === (string)$key) ? ' selected' : '';
                echo '<option value="' . e($key) . '"' . $sel . '>' . e($label) . '</option>';
            }
            echo '</select>';
            break;

        case 'url':
            echo '<input type="url"' . $attr . ' value="' . e($value) . '" placeholder="https://">';
            break;

        case 'email':
            echo '<input type="email"' . $attr . ' value="' . e($value) . '">';
            break;

        case 'color':
            echo '<span class="field-color">'
               . '<input type="color" value="' . e($value !== '' ? $value : '#00081a') . '" data-color-for="' . e($id) . '" aria-label="Colour picker">'
               . '<input type="text"' . $attr . ' value="' . e($value) . '" placeholder="#00081a">'
               . '</span>';
            break;

        case 'image':
            gns_render_image($value, $name, $id);
            break;

        default: // text, rich
            echo '<input type="text"' . $attr . ' value="' . e($value) . '">';
    }
}

/** Path plus a thumbnail, a picker and an upload button. */
function gns_render_image($value, $name, $id)
{
    echo '<div class="imagefield" data-imagefield>';
    echo '<div class="imagefield-preview" data-preview>';
    if (trim((string)$value) !== '') {
        echo '<img src="' . e($value) . '" alt="">';
    } else {
        echo '<span>none</span>';
    }
    echo '</div>';
    echo '<div class="imagefield-controls">';
    echo '<input type="text" name="' . e($name) . '" id="' . e($id) . '" value="' . e($value) . '" placeholder="/assets/uploads/photo.jpg" data-image-path>';
    echo '<div class="imagefield-row">';
    echo '<button type="button" class="btn btn-quiet btn-xs" data-image-pick>Choose</button>';
    echo '<button type="button" class="btn btn-quiet btn-xs" data-image-upload>Upload</button>';
    echo '<button type="button" class="btn btn-quiet btn-xs" data-image-clear>Clear</button>';
    echo '</div></div></div>';
}

/** A repeatable list, plus the hidden template the Add button clones. */
function gns_render_list(array $field, array $rows)
{
    $path = $field['p'];
    $simple = !empty($field['simple']);

    // Tells the save that this list really was on the form. Without it a list
    // missing from the POST for any reason would be read as "the user deleted
    // every row" and silently wipe the section.
    echo '<input type="hidden" name="present[' . e($path) . ']" value="1">';

    echo '<div class="list' . ($simple ? ' list-simple' : '') . '" data-list data-path="' . e($path) . '">';

    if (!$rows) {
        echo '<p class="list-empty" data-list-empty>Nothing here yet.</p>';
    }
    foreach ($rows as $i => $row) {
        gns_render_list_row($field, $row, $i);
    }

    echo '</div>';

    echo '<template data-list-template="' . e($path) . '">';
    gns_render_list_row($field, null, '__i__');
    echo '</template>';

    echo '<div class="row"><button type="button" class="btn btn-ghost btn-xs" data-list-add="' . e($path) . '">'
       . e(isset($field['add']) ? $field['add'] : 'Add') . '</button></div>';
}

function gns_render_list_row(array $field, $row, $index)
{
    $path = $field['p'];
    $simple = !empty($field['simple']);
    $base = 'l[' . $path . '][' . $index . ']';

    echo '<div class="list-row" data-list-row>';

    if ($simple) {
        $value = is_string($row) ? $row : '';
        $sub = array('t' => !empty($field['rich']) ? 'richtext' : 'text', 'l' => $field['l']);
        echo '<label class="sr-only" for="' . e(gns_field_id($path . $index)) . '">' . $field['l'] . '</label>';
        gns_render_control($sub, $value, $base . '[_v]', gns_field_id($path . $index));
        echo '<div class="list-row-tools">'
           . '<button type="button" data-row-up title="Move up" aria-label="Move up">&#9650;</button>'
           . '<button type="button" data-row-down title="Move down" aria-label="Move down">&#9660;</button>'
           . '<button type="button" class="is-danger" data-row-remove title="Remove" aria-label="Remove">&times;</button>'
           . '</div>';
        echo '</div>';
        return;
    }

    echo '<div class="list-row-head"><span data-row-label>Item</span>'
       . '<div class="list-row-tools">'
       . '<button type="button" data-row-up title="Move up" aria-label="Move up">&#9650;</button>'
       . '<button type="button" data-row-down title="Move down" aria-label="Move down">&#9660;</button>'
       . '<button type="button" class="is-danger" data-row-remove title="Remove" aria-label="Remove">&times;</button>'
       . '</div></div>';

    echo '<div class="fields">';
    foreach ($field['item'] as $sub) {
        $value = is_array($row) && array_key_exists($sub['p'], $row) ? $row[$sub['p']] : '';
        $name = $base . '[' . $sub['p'] . ']';
        $id = gns_field_id($path . '-' . $index . '-' . $sub['p']);
        $full = ($sub['t'] === 'list' || ($sub['w'] ?? '') !== 'half');

        echo '<div class="field' . ($full ? ' is-full' : '') . '">';
        if ($sub['t'] === 'toggle') {
            echo '<span class="label">' . $sub['l'] . '</span>';
        } else {
            echo '<label for="' . e($id) . '">' . $sub['l'] . '</label>';
        }

        if ($sub['t'] === 'list') {
            // A list inside a list row: the privacy page's paragraphs, for
            // instance. Rendered as one textarea, one line per entry, because
            // nesting repeaters two deep is more confusing than it is useful.
            $lines = is_array($value) ? implode("\n", $value) : (string)$value;
            echo '<textarea name="' . e($name) . '" id="' . e($id) . '" rows="5" data-lines>' . e($lines) . '</textarea>';
            echo '<p class="help">One paragraph per line. Blank lines are ignored.</p>';
        } else {
            gns_render_control($sub, $value, $name, $id);
            if (!empty($sub['h'])) {
                echo '<p class="help">' . $sub['h'] . '</p>';
            }
        }
        echo '</div>';
    }
    echo '</div></div>';
}
