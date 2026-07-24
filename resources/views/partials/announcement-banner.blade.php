@if(isset($activeAnnouncements) && $activeAnnouncements->isNotEmpty())
<div id="announcementBanners" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
    @foreach($activeAnnouncements as $announcement)
    <div class="announcement-banner" data-announcement-id="{{ $announcement->id }}" style="display:none; align-items:flex-start; gap:12px; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.25); border-radius: 12px; padding: 14px 16px;">
        <i data-lucide="megaphone" style="width:18px;height:18px;color:#3b82f6;flex-shrink:0;margin-top:2px;"></i>
        <div style="flex:1; min-width:0;">
            <strong style="display:block; font-size:0.88rem; color: var(--white); margin-bottom:2px;">{{ $announcement->title }}</strong>
            <p style="margin:0; font-size:0.82rem; color: var(--grey); line-height:1.5;">{{ $announcement->message }}</p>
        </div>
        <button type="button" onclick="dismissAnnouncement({{ $announcement->id }})" style="background:none;border:none;color:var(--grey);cursor:pointer;font-size:1.1rem;line-height:1;flex-shrink:0;">&times;</button>
    </div>
    @endforeach
</div>
<script>
    (function () {
        document.querySelectorAll('.announcement-banner').forEach((el) => {
            const id = el.dataset.announcementId;
            if (localStorage.getItem('dismissed-announcement-' + id) !== '1') {
                el.style.display = 'flex';
            }
        });
    })();

    function dismissAnnouncement(id) {
        localStorage.setItem('dismissed-announcement-' + id, '1');
        const el = document.querySelector('.announcement-banner[data-announcement-id="' + id + '"]');
        if (el) el.style.display = 'none';
    }
</script>
@endif
