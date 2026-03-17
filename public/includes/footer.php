</main>

<footer class="bg-white border-t border-gray-200 py-8 mt-12">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-gray-500 text-sm">© <?php echo date('Y'); ?> Nestlé Lanka PLC. NDRC Platform v1.0 (MVP)</p>
    </div>
</footer>

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notifDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.matches('button') && !event.target.closest('#notifDropdown')) {
        const dropdowns = document.getElementsByClassName("notifDropdown");
        for (let i = 0; i < dropdowns.length; i++) {
            let openDropdown = dropdowns[i];
            if (!openDropdown.classList.contains('hidden')) {
                openDropdown.classList.add('hidden');
            }
        }
    }
}

// Service Worker Registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('SW registered:', registration);
      })
      .catch((error) => {
        console.log('SW registration failed:', error);
      });
  });
}

// Poll for notifications
function checkNotifications() {
    if (document.getElementById('notifBadge')) {
        fetch('/api/notifications/count.php')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notifBadge');
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            })
            .catch(e => console.error('Notif check failed', e));
    }
}

// Check every 60 seconds
setInterval(checkNotifications, 60000);
checkNotifications();
</script>
</body>
</html>
