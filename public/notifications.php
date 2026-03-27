<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

checkAuth(); // All logged in users can see this

$user_id = $_SESSION['user_id'];

// Mark all as read if requested
if (isset($_GET['mark_all_read'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET read_status = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
    header('Location: ' . BASE_URL . 'notifications.php');
    exit();
}

// Fetch all notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Notification Center</h2>
            <p class="text-sm text-gray-500">Stay updated on your distribution activities</p>
        </div>
        <?php if (!empty($notifications)): ?>
            <a href="?mark_all_read=1" class="text-sm font-bold text-nestle-blue hover:underline">Mark all as read</a>
        <?php endif; ?>
    </div>

    <div class="bg-white shadow rounded-2xl border border-gray-100 overflow-hidden">
        <?php if (empty($notifications)): ?>
            <div class="p-20 text-center">
                <div class="text-5xl mb-4">📭</div>
                <p class="text-gray-500">No notifications yet.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($notifications as $n): ?>
                    <div class="p-6 transition-colors hover:bg-gray-50 <?php echo $n['read_status'] == 0 ? 'bg-blue-50/20' : ''; ?>">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <?php 
                                switch($n['type']) {
                                    case 'order_status': echo '<span class="text-xl">📦</span>'; break;
                                    case 'stock_alert': echo '<span class="text-xl">⚠️</span>'; break;
                                    case 'system': echo '<span class="text-xl">⚙️</span>'; break;
                                    default: echo '<span class="text-xl">🔔</span>';
                                }
                                ?>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex justify-between items-center mb-1">
                                    <h4 class="text-sm font-bold text-gray-900"><?php echo h($n['title']); ?></h4>
                                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-widest">
                                        <?php 
                                        $time = strtotime($n['created_at']);
                                        echo date('M d, H:i', $time);
                                        ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 line-clamp-2"><?php echo h($n['message']); ?></p>
                                <?php if ($n['link']): ?>
                                    <a href="<?php echo h($n['link']); ?>" class="mt-3 inline-block text-xs font-bold text-nestle-blue hover:underline">View Details →</a>
                                <?php endif; ?>
                            </div>
                            <?php if ($n['read_status'] == 0): ?>
                                <div class="ml-4 flex-shrink-0">
                                    <span class="h-2 w-2 bg-nestle-blue rounded-full block"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
