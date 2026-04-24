import { get_vmb_categories, get_vmb_specials, save_specials } from './vmb-widgets-ajax';

const load_categories = ( categories = '' ) => {
    const tableBody = document.getElementById('specialsCategory').getElementsByTagName('tbody')[0];

    if (categories) {
        tableBody.innerHTML = '';
        // Sort categories alphabetically by name
        const sortedCategories = categories.sort((a, b) => a.name.localeCompare(b.name));
        sortedCategories.forEach((category, index) => {
            buildCategoryTable(tableBody, category, index);
        });
    } else {
        get_vmb_categories().then((categories) => {
            tableBody.innerHTML = '';
            const sortedCategories = JSON.parse(categories).sort((a, b) => a.name.localeCompare(b.name));
            
            sortedCategories.forEach((category, index) => {
                build_category_table(tableBody, category, index);
            });
        });
    }
}

const load_specials = () => {
    const tableBody = document.getElementById('specialsTable').getElementsByTagName('tbody')[0];

    get_vmb_specials().then((specials) => {
        
        const parsedSpecials = JSON.parse(specials);
        build_specials_table(tableBody, parsedSpecials);

    });
}

const edit_special = (visualIndex) => {
    const dataTable = jQuery('#specialsTable').DataTable();
    const row = dataTable.row(visualIndex).node();
    const cells = row.getElementsByTagName('td');

    const id = cells[0].textContent;    
    const name = cells[1].textContent;
    const description = cells[2].textContent;

    document.getElementById('specialId').value = id;
    document.getElementById('specialName').value = name;
    document.getElementById('specialDescription').value = description;
    document.getElementById('specialDisable').checked = row.getAttribute('data-disable') === 'true';

    document.getElementById('editSpecialIndex').value = visualIndex;
    document.getElementById('specialModalLabel').textContent = 'Edit Special';

    var specialModal = new bootstrap.Modal(document.getElementById('specialModal'));
    specialModal.show();
}

const build_category_table = (tableBody, category, index) => {
    const newRow = tableBody.insertRow();
    const nameCell = newRow.insertCell(0);
    const slugCell = newRow.insertCell(1);
    const actionCell = newRow.insertCell(2);

    nameCell.textContent = category.name;
    slugCell.textContent = category.slug;
    actionCell.innerHTML = `
        <button class="btn btn-sm btn-info" onclick="window.location.href='/${vmb_ajax.cached_category_slug || 'specialcode'}/${category.slug}'">View</button>
    `;
}

const build_specials_table = (tableBody, specials, index) => {

    tableBody.innerHTML = '';

    specials.forEach((special, index) => {
        const newRow = tableBody.insertRow();
        newRow.setAttribute('data-modified', special.modified); // Add modified attribute
        newRow.setAttribute('data-disable', special.disable); // new update: Add disable attribute
        newRow.innerHTML = `
            <td>${special.id}</td>
            <td>${special.name}</td>
            <td>${special.description}</td>
            <td>${window.formatDate(special.expiration)}</td>
            <td>${Array.isArray(special.category) ? special.category.join(', ') : special.category }</td>
            <td class="action-buttons">
                <button class="btn btn-sm btn-warning" onclick="window.edit_special(${index})">Edit</button>
                <button class="btn btn-sm ${special.disable ? 'btn-danger' : 'btn-secondary'}" onclick="toggle_disable_special(${index}, this)">
                    ${special.disable ? 'Disabled' : 'Disable'}
                </button>
            </td>
        `; 
    });

    jQuery(document).ready(function() {
        const dataTable = jQuery('#specialsTable').DataTable({
            "paging": false,
            "select": true,
            "lengthChange": false, // Disable the default length change dropdown
        });
    });
}   

const toggle_disable_special = (index, button) => { 
    const dataTable = jQuery('#specialsTable').DataTable();
    const row = dataTable.row(index).node();
    const isDisabled = row.getAttribute('data-disable') === 'true';

    row.setAttribute('data-disable', !isDisabled);
    row.setAttribute('data-updated', 'true'); 
    button.className = `btn btn-sm ${!isDisabled ? 'btn-danger' : 'btn-secondary'}`;
    button.textContent = !isDisabled ? 'Disabled' : 'Disable';
    
    // Update the cached specials in the server
    const specials = [];
    let modifiedSpecial = [];
    const rows = dataTable.rows().nodes(); // Get all rows as an array of nodes

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const updated = row.getAttribute('data-updated') === 'true';

        const special = {
            id: row.cells[0].textContent,
            name: row.cells[1].textContent,
            description: row.cells[2].textContent,
            expiration: row.cells[3].textContent,
            category: row.cells[4].textContent,
            disable: row.getAttribute('data-disable') === 'true', 
            modified: row.getAttribute('data-modified') === 'true'
        };

        specials.push(special); 

        if (updated) {
            modifiedSpecial = special;
        }
    }

    save_specials(specials, modifiedSpecial);
}   


// Attach functions to the global window object
window.load_categories = load_categories;
window.load_specials = load_specials;
window.edit_special = edit_special;
window.toggle_disable_special = toggle_disable_special;
window.save_specials = save_specials;