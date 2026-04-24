export async function get_vmb_categories() {
    
    try {
        const response = await jQuery.ajax({
            url: vmb_ajax.ajax_url,
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'get_vmb_categories',
                nonce: vmb_ajax.nonce,
            }
        });

        if (response.success) {
            return response.data; 
        } else {
            console.log(response.data);
        }
    } catch (error) {
        console.log('AJAX request failed.');
    }
}

export async function get_vmb_specials() {
    try {
        const response = await jQuery.ajax({
            url: vmb_ajax.ajax_url,
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'get_vmb_specials', 
                nonce: vmb_ajax.nonce,
            }
        });

        if (response.success) {
            return response.data;
        } else {
            console.log(response.data); 
        }
    } catch (error) {
        console.log('AJAX request failed.');
    }
}

export async function save_specials(specials, modifiedSpecial = '') {
    try {
        const response = await jQuery.ajax({
            url: vmb_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'save_specials_table',
                jsonData: JSON.stringify(specials),
                modifiedSpecial: JSON.stringify(modifiedSpecial)
            }
        });

        if (response.success) {
            Swal.fire({
                title: 'Success',
                text: 'Specials saved successfully!',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            return response.data;
        } else {
            Swal.fire({
                title: 'Error',
                text: 'Failed to save specials',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.log('AJAX request failed.');
    }
}   