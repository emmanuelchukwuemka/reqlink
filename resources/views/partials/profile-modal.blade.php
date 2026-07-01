{{-- Profile Edit Modal — include in any dashboard --}}
<div id="profileModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center;">
    <div style="background:#111; border:1px solid rgba(255,255,255,0.1); border-radius:20px; width:100%; max-width:520px; margin:20px; max-height:90vh; overflow-y:auto;">

        <div style="display:flex; align-items:center; justify-content:space-between; padding:24px 28px 0;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div id="profileModalAvatar" style="width:52px; height:52px; border-radius:50%; background:linear-gradient(45deg,#E50914,#ff4d4d); display:flex; align-items:center; justify-content:center; font-size:1.4rem; font-weight:900; color:#fff; flex-shrink:0;">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <div style="font-weight:700; font-size:1.05rem;">Edit Profile</div>
                    <div style="font-size:0.8rem; color:#888;">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <button onclick="closeProfileModal()" style="background:rgba(255,255,255,0.07); border:none; color:#fff; width:34px; height:34px; border-radius:50%; cursor:pointer; font-size:1.1rem; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>

        <div id="profileModalMsg" style="display:none; margin:16px 28px 0; padding:12px 16px; border-radius:10px; font-size:0.875rem;"></div>

        <form id="profileModalForm" style="padding:20px 28px 28px;">
            @csrf
            <div style="margin-bottom:20px;">
                <div style="font-size:0.7rem; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:14px;">Basic Information</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label style="display:block; font-size:0.75rem; color:#888; margin-bottom:6px;">Full Name</label>
                        <input id="pm_name" name="name" type="text" value="{{ Auth::user()->name }}" required
                            style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:11px 14px; color:#fff; font-size:0.9rem; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.75rem; color:#888; margin-bottom:6px;">Phone Number</label>
                        <input id="pm_phone" name="phone" type="text" value="{{ Auth::user()->phone }}" required
                            style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:11px 14px; color:#fff; font-size:0.9rem; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:span 2;">
                        <label style="display:block; font-size:0.75rem; color:#888; margin-bottom:6px;">Email Address</label>
                        <input id="pm_email" name="email" type="email" value="{{ Auth::user()->email }}"
                            style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:11px 14px; color:#fff; font-size:0.9rem; box-sizing:border-box;">
                    </div>
                </div>
            </div>

            <div style="border-top:1px solid rgba(255,255,255,0.07); padding-top:20px; margin-bottom:20px;">
                <div style="font-size:0.7rem; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:.5px; margin-bottom:14px;">Change Password <span style="font-weight:400; text-transform:none; letter-spacing:0;">(leave blank to keep current)</span></div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div style="grid-column:span 2;">
                        <label style="display:block; font-size:0.75rem; color:#888; margin-bottom:6px;">Current Password</label>
                        <input id="pm_current_password" name="current_password" type="password" placeholder="Enter current password to change it"
                            style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:11px 14px; color:#fff; font-size:0.9rem; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.75rem; color:#888; margin-bottom:6px;">New Password</label>
                        <input id="pm_new_password" name="new_password" type="password" placeholder="Min 8 characters"
                            style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:11px 14px; color:#fff; font-size:0.9rem; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:0.75rem; color:#888; margin-bottom:6px;">Confirm New Password</label>
                        <input id="pm_new_password_confirmation" name="new_password_confirmation" type="password" placeholder="Repeat new password"
                            style="width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:11px 14px; color:#fff; font-size:0.9rem; box-sizing:border-box;">
                    </div>
                </div>
            </div>

            <button type="submit" id="profileModalBtn"
                style="width:100%; padding:13px; background:#E50914; color:#fff; border:none; border-radius:12px; font-size:0.95rem; font-weight:700; cursor:pointer; transition:background .2s;">
                Save Changes
            </button>
        </form>
    </div>
</div>

<script>
function openProfileModal() {
    const m = document.getElementById('profileModal');
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    document.getElementById('profileModalMsg').style.display = 'none';
    document.getElementById('pm_current_password').value = '';
    document.getElementById('pm_new_password').value = '';
    document.getElementById('pm_new_password_confirmation').value = '';
}
function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
    document.body.style.overflow = '';
}
document.getElementById('profileModal').addEventListener('click', function(e) {
    if (e.target === this) closeProfileModal();
});
document.getElementById('profileModalForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('profileModalBtn');
    const msg = document.getElementById('profileModalMsg');
    btn.disabled = true;
    btn.textContent = 'Saving…';
    msg.style.display = 'none';

    const data = new FormData(this);

    try {
        const res = await fetch('{{ route("settings.update") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
            body: data,
        });
        const json = await res.json();
        if (res.ok && json.status) {
            msg.style.background = 'rgba(34,197,94,0.12)';
            msg.style.border = '1px solid rgba(34,197,94,0.3)';
            msg.style.color = '#22c55e';
            msg.textContent = json.status;
            msg.style.display = 'block';
            // Update avatar initials and name if changed
            const newName = document.getElementById('pm_name').value.trim();
            document.querySelectorAll('.avatar, .avatar-sm, #profileModalAvatar').forEach(el => {
                if (el.textContent.trim().length === 1) el.textContent = newName.charAt(0).toUpperCase();
            });
            setTimeout(closeProfileModal, 1800);
        } else {
            const errors = json.errors ? Object.values(json.errors).flat().join(' ') : (json.message || 'Could not save. Please try again.');
            msg.style.background = 'rgba(229,9,20,0.1)';
            msg.style.border = '1px solid rgba(229,9,20,0.3)';
            msg.style.color = '#ff6b6b';
            msg.textContent = errors;
            msg.style.display = 'block';
        }
    } catch (err) {
        msg.style.background = 'rgba(229,9,20,0.1)';
        msg.style.border = '1px solid rgba(229,9,20,0.3)';
        msg.style.color = '#ff6b6b';
        msg.textContent = 'Network error. Please try again.';
        msg.style.display = 'block';
    }

    btn.disabled = false;
    btn.textContent = 'Save Changes';
});

// Make every .user-profile and .avatar-sm in the header open the modal
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.user-profile, .top-bar .avatar-sm').forEach(el => {
        el.style.cursor = 'pointer';
        el.title = 'Edit Profile';
        el.addEventListener('click', openProfileModal);
    });
});
</script>
