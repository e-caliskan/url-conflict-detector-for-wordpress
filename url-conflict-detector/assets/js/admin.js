jQuery(document).ready(function($) {
    let conflictsData = [];
    let availableTypes = {};
    
    const ajaxConfig = {
        url: urlConflictDetector.ajaxUrl,
        nonce: urlConflictDetector.nonce
    };
    
    // Load available types
    loadAvailableTypes();
    
    function loadAvailableTypes() {
        $.ajax({
            url: ajaxConfig.url,
            type: 'POST',
            data: {
                action: 'get_available_types',
                nonce: ajaxConfig.nonce
            },
            success: function(response) {
                if (response.success) {
                    availableTypes = response.data;
                    renderScanOptions();
                }
            }
        });
    }
    
    function renderScanOptions() {
        const $container = $('#scan-options-container');
        $container.empty();
        
        let html = '<div class="ucd-options-grid">';
        
        // Post Types
        if (availableTypes.post_types && availableTypes.post_types.length > 0) {
            html += '<div class="ucd-option-group">';
            html += '<div class="ucd-select-all-group">';
            html += '<label><input type="checkbox" class="select-all-post-types"> <strong>' + urlConflictDetector.i18n.allContentTypes + '</strong></label>';
            html += '</div>';
            availableTypes.post_types.forEach(function(type) {
                html += '<div class="ucd-option-item">';
                html += '<label>';
                html += '<input type="checkbox" name="scan_types[]" value="' + type.value + '" class="post-type-checkbox" checked>';
                html += type.label + ' <span style="color:#999;">(' + type.count + ')</span>';
                html += '</label>';
                html += '</div>';
            });
            html += '</div>';
        }
        
        // Taxonomies
        if (availableTypes.taxonomies && availableTypes.taxonomies.length > 0) {
            html += '<div class="ucd-option-group">';
            html += '<div class="ucd-select-all-group">';
            html += '<label><input type="checkbox" class="select-all-taxonomies"> <strong>' + urlConflictDetector.i18n.allTaxonomies + '</strong></label>';
            html += '</div>';
            availableTypes.taxonomies.forEach(function(tax) {
                html += '<div class="ucd-option-item">';
                html += '<label>';
                html += '<input type="checkbox" name="scan_types[]" value="taxonomy_' + tax.value + '" class="taxonomy-checkbox" checked>';
                html += tax.label + ' <span style="color:#999;">(' + tax.count + ')</span>';
                html += '</label>';
                html += '</div>';
            });
            html += '</div>';
        }
        
        html += '</div>';
        
        $container.html(html);
    }
    
    // Select/deselect all - Post Types
    $(document).on('change', '.select-all-post-types', function() {
        $('.post-type-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Select/deselect all - Taxonomies
    $(document).on('change', '.select-all-taxonomies', function() {
        $('.taxonomy-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Update parent checkbox when child changes
    $(document).on('change', '.post-type-checkbox', function() {
        const total = $('.post-type-checkbox').length;
        const checked = $('.post-type-checkbox:checked').length;
        $('.select-all-post-types').prop('checked', total === checked);
    });
    
    $(document).on('change', '.taxonomy-checkbox', function() {
        const total = $('.taxonomy-checkbox').length;
        const checked = $('.taxonomy-checkbox:checked').length;
        $('.select-all-taxonomies').prop('checked', total === checked);
    });
    
    // Start scan
    $('#scan-conflicts').on('click', function() {
        const selectedTypes = [];
        $('input[name="scan_types[]"]:checked').each(function() {
            selectedTypes.push($(this).val());
        });
        
        if (selectedTypes.length === 0) {
            alert(urlConflictDetector.i18n.selectAtLeastOne);
            return;
        }
        
        const $btn = $(this);
        const $progress = $('#scan-progress');
        const $summary = $('#conflicts-summary');
        
        $btn.prop('disabled', true);
        $progress.show();
        $('.progress-fill').css('width', '100%');
        
        $.ajax({
            url: ajaxConfig.url,
            type: 'POST',
            data: {
                action: 'scan_conflicts',
                nonce: ajaxConfig.nonce,
                scan_types: selectedTypes
            },
            success: function(response) {
                if (response.success) {
                    conflictsData = response.data.conflicts;
                    updateConflictsList(conflictsData);
                    updateStats(response.data.stats);
                    $summary.show();
                } else {
                    alert(urlConflictDetector.i18n.error + ': ' + response.data);
                }
            },
            error: function() {
                alert(urlConflictDetector.i18n.scanError);
            },
            complete: function() {
                $btn.prop('disabled', false);
                $progress.hide();
                $('.progress-fill').css('width', '0%');
            }
        });
    });
    
    // Update conflicts list
    function updateConflictsList(conflicts) {
        const $tbody = $('#conflicts-tbody');
        $tbody.empty();
        
        if (conflicts.length === 0) {
            $tbody.append(`
                <tr>
                    <td colspan="5" style="text-align:center; padding:40px; color:#46b450;">
                        <span class="dashicons dashicons-yes-alt" style="font-size:48px;"></span>
                        <p style="font-size:16px; margin-top:10px;">` + urlConflictDetector.i18n.noConflicts + `</p>
                    </td>
                </tr>
            `);
            return;
        }
        
        conflicts.forEach(function(conflict) {
            const statusClass = conflict.status === 'resolved' ? 'status-resolved' : 'status-pending';
            const statusText = conflict.status === 'resolved' ? urlConflictDetector.i18n.resolved : urlConflictDetector.i18n.pending;
            
            $tbody.append(`
                <tr data-conflict-id="${conflict.id}">
                    <td>
                        <code class="conflict-url">${conflict.url}</code>
                    </td>
                    <td>
                        <span class="conflict-type type-${conflict.type_1.replace('taxonomy_', '')}">${getTypeLabel(conflict.type_1)}</span>
                        <br><strong>${conflict.title_1}</strong>
                    </td>
                    <td>
                        <span class="conflict-type type-${conflict.type_2.replace('taxonomy_', '')}">${getTypeLabel(conflict.type_2)}</span>
                        <br><strong>${conflict.title_2}</strong>
                    </td>
                    <td>
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </td>
                    <td>
                        ${conflict.status === 'pending' ? `
                            <button class="button fix-conflict" data-id="${conflict.id}">` + urlConflictDetector.i18n.fix + `</button>
                        ` : `
                            <span style="color:#46b450;">✓ ` + urlConflictDetector.i18n.fixed + `</span>
                        `}
                    </td>
                </tr>
            `);
        });
    }
    
    // Update statistics
    function updateStats(stats) {
        $('#total-conflicts').text(stats.total);
        $('#pending-conflicts').text(stats.pending);
        $('#resolved-conflicts').text(stats.resolved);
    }
    
    // Translate type labels
    function getTypeLabel(type) {
        // Post types
        for (let i = 0; i < availableTypes.post_types.length; i++) {
            if (availableTypes.post_types[i].value === type) {
                return availableTypes.post_types[i].label;
            }
        }
        
        // Taxonomies
        for (let i = 0; i < availableTypes.taxonomies.length; i++) {
            if ('taxonomy_' + availableTypes.taxonomies[i].value === type) {
                return availableTypes.taxonomies[i].label;
            }
        }
        
        return type;
    }
    
    // Open fix modal
    $(document).on('click', '.fix-conflict', function() {
        const conflictId = $(this).data('id');
        openFixModal(conflictId);
    });
    
    function openFixModal(conflictId) {
        $.ajax({
            url: ajaxConfig.url,
            type: 'POST',
            data: {
                action: 'get_conflict_details',
                nonce: ajaxConfig.nonce,
                conflict_id: conflictId
            },
            success: function(response) {
                if (response.success) {
                    const conflict = response.data;
                    showFixModal(conflict);
                }
            }
        });
    }
    
    function showFixModal(conflict) {
        const modalBody = `
            <div class="conflict-details">
                <p><strong>` + urlConflictDetector.i18n.conflictingUrl + `:</strong> <code class="conflict-url">${conflict.url}</code></p>
                
                <div class="conflict-item">
                    <h4>
                        <span class="conflict-type type-${conflict.type_1.replace('taxonomy_', '')}">${getTypeLabel(conflict.type_1)}</span>
                        ${conflict.title_1}
                    </h4>
                    <div class="slug-input-group">
                        <label>` + urlConflictDetector.i18n.newSlug + `:</label>
                        <input type="text" id="new-slug-1" value="${conflict.url}-1" class="slug-input">
                    </div>
                    <button class="button button-primary apply-fix" 
                            data-conflict-id="${conflict.id}"
                            data-item-type="${conflict.type_1}"
                            data-item-id="${conflict.id_1}"
                            data-slug-input="new-slug-1">
                        ` + urlConflictDetector.i18n.updateItem + `
                    </button>
                </div>
                
                <div class="conflict-item">
                    <h4>
                        <span class="conflict-type type-${conflict.type_2.replace('taxonomy_', '')}">${getTypeLabel(conflict.type_2)}</span>
                        ${conflict.title_2}
                    </h4>
                    <div class="slug-input-group">
                        <label>` + urlConflictDetector.i18n.newSlug + `:</label>
                        <input type="text" id="new-slug-2" value="${conflict.url}-2" class="slug-input">
                    </div>
                    <button class="button button-primary apply-fix"
                            data-conflict-id="${conflict.id}"
                            data-item-type="${conflict.type_2}"
                            data-item-id="${conflict.id_2}"
                            data-slug-input="new-slug-2">
                        ` + urlConflictDetector.i18n.updateItem + `
                    </button>
                </div>
                
                <div class="fix-actions">
                    <button class="button" id="close-modal">` + urlConflictDetector.i18n.cancel + `</button>
                </div>
            </div>
        `;
        
        $('#fix-modal-body').html(modalBody);
        $('#fix-modal').show();
    }
    
    // Close modal
    $(document).on('click', '.ucd-close, #close-modal', function() {
        $('#fix-modal').hide();
    });
    
    $(window).on('click', function(e) {
        if ($(e.target).is('#fix-modal')) {
            $('#fix-modal').hide();
        }
    });
    
    // Apply fix
    $(document).on('click', '.apply-fix', function() {
        const $btn = $(this);
        const conflictId = $btn.data('conflict-id');
        const itemType = $btn.data('item-type');
        const itemId = $btn.data('item-id');
        const slugInput = $btn.data('slug-input');
        const newSlug = $('#' + slugInput).val();
        
        if (!newSlug || newSlug.trim() === '') {
            alert(urlConflictDetector.i18n.enterValidSlug);
            return;
        }
        
        $btn.prop('disabled', true).text(urlConflictDetector.i18n.updating);
        
        $.ajax({
            url: ajaxConfig.url,
            type: 'POST',
            data: {
                action: 'fix_conflict',
                nonce: ajaxConfig.nonce,
                conflict_id: conflictId,
                item_type: itemType,
                item_id: itemId,
                new_slug: newSlug
            },
            success: function(response) {
                if (response.success) {
                    alert(urlConflictDetector.i18n.slugUpdated);
                    $('#fix-modal').hide();
                    // Refresh list
                    $('#scan-conflicts').click();
                } else {
                    alert(urlConflictDetector.i18n.error + ': ' + response.data);
                    $btn.prop('disabled', false).text(urlConflictDetector.i18n.updateItem);
                }
            },
            error: function() {
                alert(urlConflictDetector.i18n.errorOccurred);
                $btn.prop('disabled', false).text(urlConflictDetector.i18n.updateItem);
            }
        });
    });
});
