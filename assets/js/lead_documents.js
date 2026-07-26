/* =========================================================
   Lead Management System - Dynamic Documents & Dropdowns JS
   ========================================================= */

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. Dynamic 30 Document Fields Builder ---
    const docContainer = document.getElementById('documents-wrapper');
    const addDocBtn = document.getElementById('btn-add-document');
    const docCounterLabel = document.getElementById('doc-counter-label');
    const MAX_DOCS = 30;

    function updateDocCounter() {
        if (!docContainer) return;
        const currentCount = docContainer.querySelectorAll('.doc-row').length;
        if (docCounterLabel) {
            docCounterLabel.textContent = `${currentCount} / ${MAX_DOCS} Attached`;
        }
        if (addDocBtn) {
            if (currentCount >= MAX_DOCS) {
                addDocBtn.disabled = true;
                addDocBtn.style.opacity = '0.5';
            } else {
                addDocBtn.disabled = false;
                addDocBtn.style.opacity = '1';
            }
        }
    }

    if (addDocBtn && docContainer) {
        addDocBtn.addEventListener('click', function() {
            const currentCount = docContainer.querySelectorAll('.doc-row').length;
            if (currentCount >= MAX_DOCS) {
                alert('Maximum limit of 30 document attachments reached.');
                return;
            }

            const rowId = Date.now();
            const rowDiv = document.createElement('div');
            rowDiv.className = 'doc-row';
            rowDiv.id = `doc-row-${rowId}`;
            rowDiv.innerHTML = `
                <div>
                    <input type="text" name="doc_titles[]" class="form-control" placeholder="Document Title (e.g., GST Certificate, ID Proof, Proposal)" required>
                </div>
                <div>
                    <input type="file" name="doc_files[]" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" required>
                </div>
                <div>
                    <button type="button" class="btn-remove-doc" onclick="removeDocRow('${rowId}')" title="Remove Field">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            `;
            docContainer.appendChild(rowDiv);
            updateDocCounter();
        });
    }

    window.removeDocRow = function(rowId) {
        const row = document.getElementById(`doc-row-${rowId}`);
        if (row) {
            row.remove();
            updateDocCounter();
        }
    };

    // Initialize counter on load
    updateDocCounter();

    // --- 2. Dependent Dropdowns (Country -> State -> City) ---
    const countrySelect = document.getElementById('country_id');
    const stateSelect = document.getElementById('state_id');
    const citySelect = document.getElementById('city_id');

    if (countrySelect && stateSelect && citySelect) {
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            stateSelect.innerHTML = '<option value="">-- Select State --</option>';
            citySelect.innerHTML = '<option value="">-- Select City --</option>';

            if (countryId) {
                fetch(`/CRM%20P/api/get_states.php?country_id=${countryId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(state => {
                            const opt = document.createElement('option');
                            opt.value = state.id;
                            opt.textContent = state.state_name;
                            stateSelect.appendChild(opt);
                        });
                    })
                    .catch(err => console.error('Error loading states:', err));
            }
        });

        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            citySelect.innerHTML = '<option value="">-- Select City --</option>';

            if (stateId) {
                fetch(`/CRM%20P/api/get_cities.php?state_id=${stateId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(city => {
                            const opt = document.createElement('option');
                            opt.value = city.id;
                            opt.textContent = city.city_name;
                            citySelect.appendChild(opt);
                        });
                    })
                    .catch(err => console.error('Error loading cities:', err));
            }
        });
    }

    // --- 3. Client-side Table Live Filter / Search ---
    const tableSearchInput = document.getElementById('tableSearchInput');
    const filterTable = document.querySelector('.custom-table');

    if (tableSearchInput && filterTable) {
        tableSearchInput.addEventListener('keyup', function() {
            const query = this.value.toLowerCase().trim();
            const rows = filterTable.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
