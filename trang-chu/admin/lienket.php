<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_name'])) {
    header('Location: login_admin.php');
    exit();
}
$adminName = $_SESSION['admin_name'];

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* ================== ACTIONS ================== */
$success = '';
$error = '';

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_shipping') {
        $id = (int)($_POST['id'] ?? 0);
        $ten = trim($_POST['ten_don_vi'] ?? '');
        $diachi = trim($_POST['dia_chi'] ?? '');
        $ghichu = trim($_POST['ghi_chu'] ?? '');
        $active = (int)($_POST['active'] ?? 0);

        if ($ten === '') {
            $error = "❌ Vui lòng nhập tên đơn vị vận chuyển.";
        } else {
            try {
                $conn->beginTransaction();

                if ($id > 0) {
                    $stmt = $conn->prepare("
                        UPDATE lienket
                        SET ten_don_vi = ?, dia_chi = ?, ghi_chu = ?, trang_thai = ?
                        WHERE id = ? AND loai = 'vanchuyen'
                    ");
                    $stmt->execute([$ten, $diachi ?: null, $ghichu ?: null, $active ? 1 : 0, $id]);
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO lienket (loai, ten_don_vi, dia_chi, ghi_chu, trang_thai)
                        VALUES ('vanchuyen', ?, ?, ?, ?)
                    ");
                    $stmt->execute([$ten, $diachi ?: null, $ghichu ?: null, $active ? 1 : 0]);
                    $id = (int)$conn->lastInsertId();
                }

                // Nếu bật active => tắt active của các đơn vị vận chuyển khác (chỉ 1 active)
                if ($active) {
                    $stmt = $conn->prepare("
                        UPDATE lienket
                        SET trang_thai = 0
                        WHERE loai = 'vanchuyen' AND id != ?
                    ");
                    $stmt->execute([$id]);
                }

                $conn->commit();
                $success = "✅ Đã lưu đơn vị vận chuyển.";
            } catch (Exception $e) {
                $conn->rollBack();
                $error = "❌ Lỗi lưu dữ liệu: " . $e->getMessage();
            }
        }
    }

    if ($action === 'set_active_shipping') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $conn->beginTransaction();
                $conn->prepare("UPDATE lienket SET trang_thai = 0 WHERE loai='vanchuyen'")->execute();
                $conn->prepare("UPDATE lienket SET trang_thai = 1 WHERE loai='vanchuyen' AND id=?")->execute([$id]);
                $conn->commit();
                $success = "✅ Đã chuyển đơn vị vận chuyển đang dùng.";
            } catch (Exception $e) {
                $conn->rollBack();
                $error = "❌ Lỗi cập nhật trạng thái: " . $e->getMessage();
            }
        }
    }

    if ($action === 'delete_shipping') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM lienket WHERE id = ? AND loai='vanchuyen'");
            $stmt->execute([$id]);
            $success = "✅ Đã xóa đơn vị vận chuyển.";
        }
    }
}

/* ================== LOAD DATA ================== */
// Shipping list
$stmt = $conn->query("SELECT * FROM lienket WHERE loai='vanchuyen' ORDER BY trang_thai DESC, updated_at DESC");
$shippings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Active shipping
$activeShipping = null;
foreach ($shippings as $s) {
    if ((int)$s['trang_thai'] === 1) {
        $activeShipping = $s;
        break;
    }
}

// Edit mode (optional)
$editId = (int)($_GET['edit_shipping'] ?? 0);
$editShipping = null;
if ($editId > 0) {
    $stmt = $conn->prepare("SELECT * FROM lienket WHERE id=? AND loai='vanchuyen' LIMIT 1");
    $stmt->execute([$editId]);
    $editShipping = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Partners (doitac) – hiện tại để trống thì show “Chưa có đối tác”
$stmt = $conn->query("SELECT * FROM lienket WHERE loai='doitac' ORDER BY updated_at DESC");
$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Liên kết</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="admin.css">

    <style>
        .block-title {
            font-weight: 800;
        }

        .mini-muted {
            font-size: .9rem;
            color: #6c757d;
        }

        .cardx {
            border: 1px solid rgba(0, 0, 0, .06);
            border-radius: 14px;
            background: #fff;
        }

        .cardx-head {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(0, 0, 0, .06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .cardx-body {
            padding: 16px;
        }

        .ship-row {
            border: 1px solid rgba(0, 0, 0, .06);
            border-radius: 12px;
            padding: 12px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .ship-ico {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(13, 110, 253, .08);
            color: #0d6efd;
            flex: 0 0 auto;
        }

        .ship-main {
            flex: 1 1 auto;
            min-width: 0;
        }

        .ship-name {
            font-weight: 800;
        }

        .badge-active {
            background: rgba(25, 135, 84, .12);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, .2);
            font-weight: 700;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: flex-end;
        }

        .partner-empty {
            border: 1px dashed rgba(0, 0, 0, .18);
            border-radius: 14px;
            padding: 16px;
            background: #fff;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="home.php" class="admin-sidebar-brand">
                    <div class="admin-sidebar-logo">NS</div>
                    <div class="admin-sidebar-title">NASA Admin</div>
                </a>
            </div>

            <nav class="admin-sidebar-nav">
                <a href="home.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'home.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-house"></i><span>Trang chủ</span>
                </a>
                <a href="donhang.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'donhang.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-clipboard"></i><span>Đơn hàng</span>
                </a>
                <a href="sanpham.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'sanpham.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-box"></i><span>Sản phẩm</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khuyenmai.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khuyenmai.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bullhorn"></i><span>Khuyến mãi</span>
                </a>

                <a href="doanhthu.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'doanhthu.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-database"></i><span>La bàn dữ liệu</span>
                </a>
                <a href="qladmin.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'qladmin.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-id-badge"></i><span>Tình trạng tài khoản</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="khachhang.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'khachhang.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i><span>Khách hàng</span>
                </a>
                <a href="danhgia.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'danhgia.php' ? 'active' : ''; ?>">
                    <i class="fa-regular fa-star"></i><span>Đánh giá</span>
                </a>
                <a href="lienket.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'lienket.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-link"></i><span>Liên kết</span>
                </a>

                <div class="admin-sidebar-divider"></div>

                <a href="logout.php" class="admin-nav-item admin-sidebar-logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i><span>Đăng xuất</span>
                </a>
            </nav>

            <div class="admin-sidebar-footer">© <?php echo date('Y'); ?> Nasa Shop</div>
        </aside>

        <!-- MAIN -->
        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <div class="admin-page-title">Liên kết</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Xin chào, <strong><?php echo h($adminName); ?></strong></span>
                </div>
            </header>

            <main class="admin-content">

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo h($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo h($error); ?></div>
                <?php endif; ?>

                <!-- VẬN CHUYỂN -->
                <div class="cardx mb-3">
                    <div class="cardx-head">
                        <div>
                            <div class="block-title">Đơn vị vận chuyển</div>
                            <div class="mini-muted">Bạn có thể thêm nhiều đơn vị, và chọn 1 đơn vị “đang dùng”.</div>
                        </div>
                        <span class="mini-muted">
                            Đang dùng:
                            <strong><?php echo h($activeShipping['ten_don_vi'] ?? 'Chưa chọn'); ?></strong>
                        </span>
                    </div>

                    <div class="cardx-body">
                        <div class="row g-3">
                            <!-- Danh sách đơn vị -->
                            <div class="col-lg-7">
                                <?php if (empty($shippings)): ?>
                                    <div class="text-muted">Chưa có đơn vị vận chuyển. Hãy thêm mới ở bên phải.</div>
                                <?php else: ?>
                                    <?php foreach ($shippings as $s): ?>
                                        <div class="ship-row">
                                            <div class="ship-ico"><i class="fa-solid fa-truck-fast"></i></div>
                                            <div class="ship-main">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <div class="ship-name"><?php echo h($s['ten_don_vi']); ?></div>
                                                    <?php if ((int)$s['trang_thai'] === 1): ?>
                                                        <span class="badge badge-active rounded-pill px-2 py-1">Đang dùng</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($s['dia_chi'])): ?>
                                                    <div class="mini-muted mt-1"><i class="fa-solid fa-location-dot me-1"></i><?php echo h($s['dia_chi']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($s['ghi_chu'])): ?>
                                                    <div class="mini-muted mt-1"><?php echo h($s['ghi_chu']); ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="actions">
                                                <a class="btn btn-sm btn-outline-primary"
                                                    href="lienket.php?edit_shipping=<?php echo (int)$s['id']; ?>">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </a>

                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="set_active_shipping">
                                                    <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                                                    <button class="btn btn-sm btn-success" type="submit"
                                                        <?php echo ((int)$s['trang_thai'] === 1) ? 'disabled' : ''; ?>>
                                                        <i class="fa-solid fa-check"></i> Chọn dùng
                                                    </button>
                                                </form>

                                                <form method="post" style="display:inline;"
                                                    onsubmit="return confirm('Xóa đơn vị vận chuyển này?');">
                                                    <input type="hidden" name="action" value="delete_shipping">
                                                    <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Form thêm/sửa -->
                            <div class="col-lg-5">
                                <div class="cardx" style="border-radius:14px;">
                                    <div class="cardx-head">
                                        <div class="report-card-title">
                                            <?php echo $editShipping ? 'Sửa đơn vị vận chuyển' : 'Thêm đơn vị vận chuyển'; ?>
                                        </div>
                                        <?php if ($editShipping): ?>
                                            <a class="btn btn-sm btn-outline-secondary" href="lienket.php">
                                                <i class="fa-solid fa-xmark"></i> Hủy
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cardx-body">
                                        <form method="post">
                                            <input type="hidden" name="action" value="save_shipping">
                                            <input type="hidden" name="id" value="<?php echo (int)($editShipping['id'] ?? 0); ?>">

                                            <div class="mb-2">
                                                <label class="form-label fw-semibold">Tên đơn vị</label>
                                                <input type="text" class="form-control" name="ten_don_vi"
                                                    value="<?php echo h($editShipping['ten_don_vi'] ?? ''); ?>"
                                                    placeholder="VD: J&T Express, GHN, Viettel Post..." required>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label fw-semibold">Địa chỉ kho gửi hàng</label>
                                                <input type="text" class="form-control" name="dia_chi"
                                                    value="<?php echo h($editShipping['dia_chi'] ?? ''); ?>"
                                                    placeholder="VD: 268 Lý Thường Kiệt, P.14, Q.10, TP.HCM">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Ghi chú</label>
                                                <textarea class="form-control" name="ghi_chu" rows="3"
                                                    placeholder="Giờ nhận hàng, hotline, chính sách COD..."><?php echo h($editShipping['ghi_chu'] ?? ''); ?></textarea>
                                            </div>

                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" value="1" id="activeShip" name="active"
                                                    <?php echo (!empty($editShipping) ? ((int)$editShipping['trang_thai'] === 1) : (empty($activeShipping) ? true : false)) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="activeShip">
                                                    Đặt làm đơn vị vận chuyển đang dùng
                                                </label>
                                            </div>

                                            <button class="btn btn-primary w-100" type="submit">
                                                <i class="fa-solid fa-floppy-disk"></i> Lưu
                                            </button>

                                            <div class="text-muted small mt-2">
                                                * Bạn có thể thêm nhiều đơn vị và đổi “đang dùng” bất cứ lúc nào.
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div><!-- row -->
                    </div>
                </div>

                <!-- ĐỐI TÁC -->
                <div class="cardx">
                    <div class="cardx-head">
                        <div>
                            <div class="block-title">Đối tác</div>
                            <div class="mini-muted">Bạn có thể hợp tác với các đơn vị đối tác khác.</div>
                        </div>
                    </div>
                    <div class="cardx-body">
                        <?php if (empty($partners)): ?>
                            <div class="partner-empty">
                                <div class="fw-semibold">Chưa có đối tác</div>
                            </div>
                        <?php else: ?>

                            <div class="table-responsive">
                                <table class="table table-modern align-middle">
                                    <thead>
                                        <tr>
                                            <th>Tên đối tác</th>
                                            <th>Địa chỉ</th>
                                            <th>Ghi chú</th>
                                            <th style="width:120px;">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($partners as $p): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo h($p['ten_don_vi']); ?></td>
                                                <td><?php echo h($p['dia_chi'] ?: '—'); ?></td>
                                                <td><?php echo h($p['ghi_chu'] ?: '—'); ?></td>
                                                <td>
                                                    <?php if ((int)$p['trang_thai'] === 1): ?>
                                                        <span class="badge bg-success">Đang dùng</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Tạm tắt</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>