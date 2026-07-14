<?php
session_start();

// --- KONFIGURASI ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'local_projects';

// Setting Login (Ubah ke false jika ingin mematikan login)
$use_login = true; 
$admin_pass = 'root'; 

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Logic Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
}

// Logic Login
if ($use_login && isset($_POST['login'])) {
    if ($_POST['password'] === $admin_pass) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Password salah!";
    }
}

$is_admin = !$use_login || (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true);

// CRUD Logic
if ($is_admin && isset($_POST['add_project'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $url = mysqli_real_escape_string($conn, $_POST['url']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    mysqli_query($conn, "INSERT INTO projects (title, url, description) VALUES ('$title', '$url', '$desc')");
    header("Location: index.php");
    exit;
}

if ($is_admin && isset($_POST['edit_project'])) {
    $id = $_POST['id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $url = mysqli_real_escape_string($conn, $_POST['url']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    mysqli_query($conn, "UPDATE projects SET title='$title', url='$url', description='$desc' WHERE id=$id");
    header("Location: index.php");
    exit;
}

if ($is_admin && isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM projects WHERE id=$id");
    header("Location: index.php");
    exit;
}

$projects = mysqli_query($conn, "SELECT * FROM projects ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevLocal Hub - Modern Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glass-white {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Animated gradient background */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .animated-bg {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #4facfe);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .float {
            animation: float 6s ease-in-out infinite;
        }
        
        /* Card hover effect */
        .project-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .project-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .project-card:hover::before {
            left: 100%;
        }
        
        .project-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        /* Pulse animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Slide in animation */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .slide-in {
            animation: slideInUp 0.6s ease-out forwards;
        }
        
        /* Staggered animation for cards */
        .project-card:nth-child(1) { animation-delay: 0.1s; }
        .project-card:nth-child(2) { animation-delay: 0.2s; }
        .project-card:nth-child(3) { animation-delay: 0.3s; }
        .project-card:nth-child(4) { animation-delay: 0.4s; }
        .project-card:nth-child(5) { animation-delay: 0.5s; }
        .project-card:nth-child(6) { animation-delay: 0.6s; }
        
        /* Glow effect */
        .glow {
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        }
        
        .glow:hover {
            box-shadow: 0 0 30px rgba(102, 126, 234, 0.8);
        }
        
        /* Rotate animation for icons */
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .rotate-on-hover:hover svg {
            animation: rotate 0.6s ease-in-out;
        }
        
        /* Shimmer effect */
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        
        .shimmer {
            background: linear-gradient(to right, #667eea 0%, #764ba2 50%, #667eea 100%);
            background-size: 1000px 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 3s infinite linear;
        }
        
        /* Search input focus effect */
        .search-input {
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(255,255,255,0.3);
        }
        
        /* Button ripple effect */
        .ripple {
            position: relative;
            overflow: hidden;
        }
        
        .ripple::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .ripple:active::after {
            width: 300px;
            height: 300px;
        }
        
        /* Particle background */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }
        
        .particle {
            position: absolute;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            animation: float-particle 15s infinite;
        }
        
        @keyframes float-particle {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }
        
        /* Delete button animation */
        .delete-btn {
            transition: all 0.3s ease;
        }
        
        .delete-btn:hover {
            transform: rotate(90deg) scale(1.2);
        }
        
        /* Edit button animation */
        .edit-btn {
            transition: all 0.3s ease;
        }
        
        .edit-btn:hover {
            transform: scale(1.2);
        }
        
        /* Modal animation */
        #editModal {
            transition: all 0.3s ease;
        }
        
        #editModal.flex .glass-white {
            animation: modalBounce 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        @keyframes modalBounce {
            0% {
                transform: scale(0.7);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body class="min-h-screen">

    <!-- Particle Background -->
    <div class="particles">
        <div class="particle" style="width: 10px; height: 10px; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 6px; height: 6px; left: 25%; animation-delay: 2s;"></div>
        <div class="particle" style="width: 8px; height: 8px; left: 40%; animation-delay: 4s;"></div>
        <div class="particle" style="width: 12px; height: 12px; left: 55%; animation-delay: 6s;"></div>
        <div class="particle" style="width: 7px; height: 7px; left: 70%; animation-delay: 8s;"></div>
        <div class="particle" style="width: 9px; height: 9px; left: 85%; animation-delay: 10s;"></div>
    </div>

    <nav class="glass text-white p-4 shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
                <span class="text-4xl float">🚀</span>
                <span class="shimmer">DevLocal</span><span class="font-light">Hub</span>
            </h1>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="🔍 Cari project..." 
                           class="search-input px-6 py-2.5 rounded-full text-slate-800 focus:outline-none w-72 transition-all shadow-lg">
                </div>
                <?php if($use_login && $is_admin): ?>
                    <a href="?logout" class="ripple text-sm bg-white/20 hover:bg-red-500 px-5 py-2.5 rounded-full transition-all shadow-lg hover:shadow-xl font-medium">
                        ✨ Logout
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-8 relative z-10">
        <?php if ($use_login && !$is_admin): ?>
            <div class="max-w-md mx-auto mt-32 slide-in">
                <div class="glass-white p-10 rounded-3xl shadow-2xl">
                    <div class="text-center mb-6">
                        <div class="text-6xl mb-4 float">🔐</div>
                        <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">
                            Admin Access
                        </h2>
                        <p class="text-slate-500 mt-2">Enter your password to unlock</p>
                    </div>
                    <form method="POST">
                        <input type="password" name="password" placeholder="Enter Password" 
                               class="w-full p-4 border-2 border-purple-200 rounded-xl mb-4 focus:ring-4 focus:ring-purple-300 outline-none transition-all">
                        <?php if(isset($error)): ?>
                            <p class="text-red-500 text-sm mb-3 text-center slide-in">⚠️ <?= $error ?></p>
                        <?php endif; ?>
                        <button type="submit" name="login" 
                                class="ripple w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-xl font-bold hover:from-purple-700 hover:to-pink-700 transition-all shadow-lg hover:shadow-2xl transform hover:scale-105">
                            🚀 Unlock Dashboard
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="glass-white p-6 rounded-2xl shadow-xl slide-in hover:shadow-2xl transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Total Projects</p>
                            <h3 class="text-3xl font-bold text-purple-600 mt-1"><?= mysqli_num_rows($projects) ?></h3>
                        </div>
                        <div class="text-5xl float">📊</div>
                    </div>
                </div>
                <div class="glass-white p-6 rounded-2xl shadow-xl slide-in hover:shadow-2xl transition-all" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Active Development</p>
                            <h3 class="text-3xl font-bold text-pink-600 mt-1">100%</h3>
                        </div>
                        <div class="text-5xl float" style="animation-delay: 1s;">⚡</div>
                    </div>
                </div>
                <div class="glass-white p-6 rounded-2xl shadow-xl slide-in hover:shadow-2xl transition-all" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 text-sm font-medium">Status</p>
                            <h3 class="text-3xl font-bold text-green-600 mt-1">Online</h3>
                        </div>
                        <div class="text-5xl pulse">🟢</div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-1">
                    <div class="glass-white p-8 rounded-3xl shadow-2xl border-2 border-white/50 sticky top-24 slide-in">
                        <div class="text-center mb-6">
                            <div class="text-5xl mb-3 float">➕</div>
                            <h3 class="font-bold text-xl text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">
                                Tambah Project Baru
                            </h3>
                        </div>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">NAMA PROJECT</label>
                                <input type="text" name="title" placeholder="My Awesome Project" 
                                       class="w-full p-3 border-2 border-purple-200 rounded-xl focus:ring-4 focus:ring-purple-300 outline-none text-sm transition-all" required>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">URL LOCALHOST</label>
                                <input type="text" name="url" placeholder="http://localhost/project" 
                                       class="w-full p-3 border-2 border-purple-200 rounded-xl focus:ring-4 focus:ring-purple-300 outline-none text-sm transition-all" required>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">DESKRIPSI</label>
                                <textarea name="description" placeholder="Deskripsi singkat tentang project..." 
                                          class="w-full p-3 border-2 border-purple-200 rounded-xl focus:ring-4 focus:ring-purple-300 outline-none text-sm transition-all" rows="3"></textarea>
                            </div>
                            <button type="submit" name="add_project" 
                                    class="ripple w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3.5 rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all font-bold shadow-lg hover:shadow-2xl transform hover:scale-105">
                                💾 Simpan Project
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div id="projectGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php 
                        $delay = 0;
                        while($row = mysqli_fetch_assoc($projects)): 
                        ?>
                        <div class="project-card glass-white p-6 rounded-3xl shadow-xl border-2 border-white/50 hover:border-purple-300 group slide-in" style="animation-delay: <?= $delay ?>s;">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center text-2xl shadow-lg rotate-on-hover">
                                        🎯
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-800 title"><?= $row['title'] ?></h3>
                                        <span class="text-xs text-slate-400">localhost project</span>
                                    </div>
                                </div>
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button onclick="openEditModal(<?= $row['id'] ?>, '<?= addslashes($row['title']) ?>', '<?= addslashes($row['url']) ?>', '<?= addslashes($row['description']) ?>')" 
                                       class="edit-btn text-slate-300 hover:text-blue-500 transition-all transform hover:scale-110">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </button>
                                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('⚠️ Yakin ingin menghapus project \"<?= addslashes($row['title']) ?>\"?')" 
                                       class="delete-btn text-slate-300 hover:text-red-500 transition-all transform hover:scale-110 hover:rotate-12">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <p class="text-slate-600 text-sm mb-5 line-clamp-2 desc leading-relaxed"><?= $row['description'] ?: 'No description available' ?></p>
                            <div class="flex gap-3">
                                <a href="<?= $row['url'] ?>" target="_blank" 
                                   class="flex-1 ripple inline-flex items-center justify-center bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold py-3 px-4 rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all shadow-md hover:shadow-xl transform hover:scale-105">
                                    🚀 Buka Project
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <?php 
                        $delay += 0.1;
                        endwhile; 
                        ?>
                    </div>
                    
                    <?php if(mysqli_num_rows($projects) == 0): ?>
                    <div class="text-center py-20 slide-in">
                        <div class="text-8xl mb-6 float">📦</div>
                        <h3 class="text-2xl font-bold text-white mb-2">Belum Ada Project</h3>
                        <p class="text-white/80">Tambahkan project pertama kamu sekarang!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50" onclick="closeEditModal(event)">
        <div class="glass-white p-8 rounded-3xl shadow-2xl max-w-lg w-full mx-4 transform scale-95 transition-all" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="text-4xl">✏️</div>
                    <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">
                        Edit Project
                    </h2>
                </div>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-red-500 transition-all transform hover:rotate-90">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form method="POST" class="space-y-4" id="editForm">
                <input type="hidden" name="id" id="edit_id">
                
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">NAMA PROJECT</label>
                    <input type="text" name="title" id="edit_title" placeholder="My Awesome Project" 
                           class="w-full p-3 border-2 border-purple-200 rounded-xl focus:ring-4 focus:ring-purple-300 outline-none transition-all" required>
                </div>
                
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">URL LOCALHOST</label>
                    <input type="text" name="url" id="edit_url" placeholder="http://localhost/project" 
                           class="w-full p-3 border-2 border-purple-200 rounded-xl focus:ring-4 focus:ring-purple-300 outline-none transition-all" required>
                </div>
                
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">DESKRIPSI</label>
                    <textarea name="description" id="edit_description" placeholder="Deskripsi singkat tentang project..." 
                              class="w-full p-3 border-2 border-purple-200 rounded-xl focus:ring-4 focus:ring-purple-300 outline-none transition-all" rows="4"></textarea>
                </div>
                
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" 
                            class="flex-1 px-6 py-3.5 border-2 border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 transition-all font-semibold">
                        ❌ Batal
                    </button>
                    <button type="submit" name="edit_project" 
                            class="ripple flex-1 bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3.5 rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all font-bold shadow-lg hover:shadow-2xl">
                        💾 Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Edit Modal Functions
        function openEditModal(id, title, url, description) {
            const modal = document.getElementById('editModal');
            const modalContent = modal.querySelector('.glass-white');
            
            // Set values
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_url').value = url;
            document.getElementById('edit_description').value = description;
            
            // Show modal with animation
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modalContent.style.transform = 'scale(1)';
            }, 10);
        }

        function closeEditModal(event) {
            if (event && event.target !== document.getElementById('editModal')) {
                return;
            }
            
            const modal = document.getElementById('editModal');
            const modalContent = modal.querySelector('.glass-white');
            
            modalContent.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });

        // Search functionality with smooth animation
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let cards = document.querySelectorAll('.project-card');

            cards.forEach((card, index) => {
                let title = card.querySelector('.title').innerText.toLowerCase();
                let desc = card.querySelector('.desc').innerText.toLowerCase();
                
                if (title.includes(filter) || desc.includes(filter)) {
                    card.style.display = "";
                    card.style.animation = `slideInUp 0.6s ease-out ${index * 0.1}s forwards`;
                } else {
                    card.style.opacity = "0";
                    setTimeout(() => {
                        card.style.display = "none";
                    }, 300);
                }
            });
        });

        // Add ripple effect to buttons
        document.querySelectorAll('.ripple').forEach(button => {
            button.addEventListener('click', function(e) {
                let ripple = document.createElement('span');
                ripple.classList.add('ripple-effect');
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>