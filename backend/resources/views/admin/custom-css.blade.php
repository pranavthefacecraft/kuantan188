@extends('layouts.admin')

@section('title', 'Custom CSS')

@section('content')
<div class="grid">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 600;">Custom CSS</h2>
                <p style="margin: 0.5rem 0 0 0; color: var(--on-surface-variant);">
                    Add custom CSS for the frontend site, admin panel, or both. Active entries are applied automatically.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" onclick="openAddModal()">
                    <span class="material-icons" style="font-size: 18px;">add</span>
                    Add CSS Entry
                </button>
            </div>
        </div>
    </div>

    <!-- CSS Entries List -->
    <div class="card">
        <div class="card-body">
            @if($cssEntries->isEmpty())
                <div style="text-align: center; padding: 3rem; color: var(--on-surface-variant);">
                    <span class="material-icons" style="font-size: 48px; opacity: 0.5;">css</span>
                    <p style="margin-top: 1rem;">No custom CSS entries yet. Click "Add CSS Entry" to get started.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border);">
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Name</th>
                                <th style="padding: 0.75rem; text-align: center; font-weight: 600;">Target</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Preview</th>
                                <th style="padding: 0.75rem; text-align: center; font-weight: 600;">Status</th>
                                <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Updated</th>
                                <th style="padding: 0.75rem; text-align: center; font-weight: 600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cssEntries as $entry)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.75rem; font-weight: 500;">{{ $entry->name }}</td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <span style="padding: 0.2rem 0.6rem; font-size: 0.75rem; border-radius: 20px; font-weight: 500; 
                                        {{ $entry->target === 'admin' 
                                            ? 'background-color: #FFE6ED; color: #66011d; border: 1px solid rgba(99,102,241,0.3);' 
                                            : ($entry->target === 'both' 
                                                ? 'background: rgba(245,158,11,0.1); color: var(--warning); border: 1px solid rgba(245,158,11,0.3);' 
                                                : 'background-color: #FFE6ED; color: #66011d; border: 1px solid rgba(99,102,241,0.3);') 
                                        }}">
                                        {{ ucfirst($entry->target) }}
                                    </span>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <code style="font-size: 0.8rem; background: var(--surface-variant); padding: 0.25rem 0.5rem; border-radius: 4px; display: inline-block; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ Str::limit($entry->css_content, 80) }}
                                        </code>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <button class="btn btn-sm toggle-status-btn"
                                                style="padding: 0.25rem 0.75rem; font-size: 0.8rem; border-radius: 20px; {{ $entry->is_active ? 'background: rgba(16,185,129,0.1); color: var(--success); border: 1px solid rgba(16,185,129,0.3);' : 'background: #FFE6ED; color: #66001D; border: 1px solid;' }}"
                                                onclick="toggleStatus({{ $entry->id }}, this)">
                                            {{ $entry->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td style="padding: 0.75rem; color: var(--on-surface-variant); font-size: 0.875rem;">
                                        {{ $entry->updated_at->diffForHumans() }}
                                    </td>
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                            <button class="btn btn-outline btn-sm" onclick="openEditModal({{ $entry->id }})" title="Edit">
                                                <span class="material-icons" style="font-size: 16px;">edit</span>
                                            </button>
                                            <button class="btn btn-sm" style="background: rgba(239,68,68,0.1); color: var(--error); border: 1px solid rgba(239,68,68,0.3);"
                                                    onclick="confirmDelete({{ $entry->id }})" title="Delete">
                                                <span class="material-icons" style="font-size: 16px;">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Combined CSS Preview -->
    @if($cssEntries->where('is_active', true)->isNotEmpty())
    <div class="card">
        <div class="card-body">
            <h3 style="margin: 0 0 1rem 0; font-size: 1.125rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-icons" style="color: var(--primary);">preview</span>
                Combined Active CSS (served to frontend)
            </h3>
            <pre id="combinedCssPreview" style="background: #1e1e2e; color: #cdd6f4; padding: 1.25rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.85rem; line-height: 1.6; max-height: 400px; overflow-y: auto;">{{ $cssEntries->where('is_active', true)->pluck('css_content')->implode("\n\n") }}</pre>
        </div>
    </div>
    @endif
</div>

<!-- Add/Edit Modal -->
<div id="cssModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: var(--surface); border-radius: 1rem; box-shadow: var(--shadow-lg); width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600;">Add CSS Entry</h3>
            <button onclick="closeModal()" style="background: none; border: none; cursor: pointer; padding: 0.25rem;">
                <span class="material-icons">close</span>
            </button>
        </div>
        <form id="cssForm" method="POST" action="">
            @csrf
            <input type="hidden" id="formMethod" name="_method" value="POST">
            <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
                <div class="form-group">
                    <label for="css_name" class="form-label" style="font-weight: 500; margin-bottom: 0.5rem; display: block;">Name</label>
                    <input type="text" id="css_name" name="name" class="form-control" placeholder="e.g. Homepage Hero Styles" required
                           style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid var(--border); border-radius: 0.5rem; font-size: 0.95rem;">
                </div>
                <div class="form-group">
                    <label for="css_target" class="form-label" style="font-weight: 500; margin-bottom: 0.5rem; display: block;">Apply To</label>
                    <select id="css_target" name="target" class="form-control" required
                            style="width: 100%; padding: 0.625rem 0.875rem; border: 1px solid var(--border); border-radius: 0.5rem; font-size: 0.95rem; background: var(--surface);">
                        <option value="frontend">Frontend (public site)</option>
                        <option value="admin">Admin Panel</option>
                        <option value="both">Both</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="css_content" class="form-label" style="font-weight: 500; margin-bottom: 0.5rem; display: block;">CSS Content</label>
                    <textarea id="css_content" name="css_content" rows="15" class="form-control" placeholder=".my-class {&#10;  color: #333;&#10;  font-size: 16px;&#10;}" required
                              style="width: 100%; padding: 0.875rem; border: 1px solid var(--border); border-radius: 0.5rem; font-family: 'Courier New', Courier, monospace; font-size: 0.9rem; line-height: 1.6; resize: vertical; background: #1e1e2e; color: #cdd6f4; tab-size: 2;"></textarea>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="css_is_active" name="is_active" checked
                           style="width: 18px; height: 18px; accent-color: var(--primary);">
                    <label for="css_is_active" style="font-weight: 500; cursor: pointer;">Active</label>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Save CSS Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: var(--surface); border-radius: 1rem; box-shadow: var(--shadow-lg); width: 90%; max-width: 400px;">
        <div style="padding: 2rem; text-align: center;">
            <span class="material-icons" style="font-size: 48px; color: var(--error);">warning</span>
            <h3 style="margin: 1rem 0 0.5rem;">Delete CSS Entry?</h3>
            <p style="color: var(--on-surface-variant);">This action cannot be undone.</p>
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" action="" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="background: var(--error); color: white;">Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Store entry data for JS -->
<script>
    const cssEntries = @json($cssEntries);

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add CSS Entry';
        document.getElementById('modalSubmitBtn').textContent = 'Save CSS Entry';
        document.getElementById('cssForm').action = '{{ route("admin.custom-css.store") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('css_name').value = '';
        document.getElementById('css_target').value = 'frontend';
        document.getElementById('css_content').value = '';
        document.getElementById('css_is_active').checked = true;
        document.getElementById('cssModal').style.display = 'block';
    }

    function openEditModal(id) {
        const entry = cssEntries.find(e => e.id === id);
        if (!entry) return;
        document.getElementById('modalTitle').textContent = 'Edit CSS Entry';
        document.getElementById('modalSubmitBtn').textContent = 'Update CSS Entry';
        document.getElementById('cssForm').action = '/admin/custom-css/' + id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('css_name').value = entry.name;
        document.getElementById('css_target').value = entry.target;
        document.getElementById('css_content').value = entry.css_content;
        document.getElementById('css_is_active').checked = entry.is_active;
        document.getElementById('cssModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('cssModal').style.display = 'none';
    }

    function confirmDelete(id) {
        document.getElementById('deleteForm').action = '/admin/custom-css/' + id;
        document.getElementById('deleteModal').style.display = 'block';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    function toggleStatus(id, btn) {
        fetch('/admin/custom-css/' + id + '/toggle-status', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.is_active) {
                    btn.textContent = 'Active';
                    btn.style.background = 'rgba(16,185,129,0.1)';
                    btn.style.color = 'var(--success)';
                    btn.style.borderColor = 'rgba(16,185,129,0.3)';
                } else {
                    btn.textContent = 'Inactive';
                    btn.style.background = 'rgba(239,68,68,0.1)';
                    btn.style.color = 'var(--error)';
                    btn.style.borderColor = 'rgba(239,68,68,0.3)';
                }
            }
        });
    }

    // Tab key support in textarea
    document.getElementById('css_content').addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            e.preventDefault();
            const start = this.selectionStart;
            const end = this.selectionEnd;
            this.value = this.value.substring(0, start) + '  ' + this.value.substring(end);
            this.selectionStart = this.selectionEnd = start + 2;
        }
    });

    // Close modals on backdrop click
    document.getElementById('cssModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>
@endsection
