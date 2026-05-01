// ===================================================
// Bazaar - Main Application JavaScript
// ===================================================

$(document).ready(function() {
    
    // ===== FAVORITE TOGGLE =====
    $(document).on('click', '.favorite-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        let icon = $(this).find('i');
        
        if (icon.hasClass('far')) {
            icon.removeClass('far').addClass('fas').css('color', '#ff4c4c');
            $(this).addClass('active');
            showToast('Added to favorites', 'success');
        } else {
            icon.removeClass('fas').addClass('far').css('color', '');
            $(this).removeClass('active');
            showToast('Removed from favorites', 'info');
        }
    });

    // ===== HEADER SCROLL EFFECT =====
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('header').addClass('scrolled');
        } else {
            $('header').removeClass('scrolled');
        }
    });

    // ===== AUTO-HIDE ALERT BANNERS =====
    setTimeout(function() {
        $('.alert-banner').fadeOut(400, function() { $(this).remove(); });
    }, 5000);

    // ===== IMAGE PREVIEW ON POST AD =====
    $(document).on('change', 'input[name="images[]"]', function() {
        const files = this.files;
        const preview = $(this).closest('.photo-upload');
        preview.find('.image-previews').remove();
        
        if (files.length > 0) {
            let previewHtml = '<div class="image-previews" style="display:flex; gap:10px; margin-top:15px; flex-wrap:wrap;">';
            for (let i = 0; i < Math.min(files.length, 5); i++) {
                const url = URL.createObjectURL(files[i]);
                previewHtml += `<img src="${url}" style="width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid var(--accent-cyan);">`;
            }
            previewHtml += '</div>';
            preview.append(previewHtml);
        }
    });

});

// ===== TOAST NOTIFICATION SYSTEM =====
function showToast(message, type = 'info', duration = 3500) {
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-times-circle',
        info: 'fas fa-info-circle',
        warning: 'fas fa-exclamation-triangle'
    };

    const toast = $(`
        <div class="toast toast-${type}">
            <i class="${icons[type] || icons.info}"></i>
            <span>${message}</span>
            <button class="toast-close" onclick="dismissToast(this)">&times;</button>
        </div>
    `);

    $('#toastContainer').append(toast);

    // Auto dismiss
    setTimeout(() => {
        dismissToast(toast.find('.toast-close')[0]);
    }, duration);
}

function dismissToast(btn) {
    const toast = $(btn).closest('.toast');
    toast.addClass('toast-hide');
    setTimeout(() => toast.remove(), 300);
}

// ===== CONFIRM DIALOG =====
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}
