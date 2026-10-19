<?php
/**
 * Trang "Danh sách Ban Thư Ký Đoàn" - slug /doan-thu-ky.
 * Thiết kế giao diện cao cấp, đồng bộ chuẩn nhận diện thương hiệu Đại hội Mường Thanh 2026.
 *
 * @var array $event
 * @var array $secretaries Mỗi phần tử: array('group_name' => '...', 'members' => [{name,phone,role}], 'units' => ["..."]).
 */
$base = Yii::app()->request->baseUrl;
$topHeader = $base . '/themes/hope-ui/assets/images/dashboard/top-header.png';
$topImage  = $base . '/themes/hope-ui/assets/images/dashboard/top-image.jpg';
$bgDaihoi  = $base . '/maintenance/assets/images/bg_daihoi.jpg';

$e = function ($s) {
    if (is_array($s)) {
        foreach (array('name', 'title', 'label') as $k) {
            if (isset($s[$k]) && is_scalar($s[$k])) {
                return CHtml::encode((string) $s[$k]);
            }
        }
        return '';
    }
    return CHtml::encode($s !== null ? (string) $s : '');
};

$eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');
$secretaries = isset($secretaries) && is_array($secretaries) ? $secretaries : array();

// Dự phòng dữ liệu chuẩn từ Sổ tay Đại hội nếu API chưa có dữ liệu
if (empty($secretaries) && class_exists('Daihoi')) {
    $secretaries = Daihoi::getDefaultSecretaries();
}

// Thống kê nhanh
$totalGroups = count($secretaries);
$totalMembers = 0;
$totalUnits = 0;
foreach ($secretaries as $g) {
    if (!empty($g['members']) && is_array($g['members'])) {
        $totalMembers += count($g['members']);
    }
    if (!empty($g['units']) && is_array($g['units'])) {
        $totalUnits += count($g['units']);
    }
}

// Helper lấy chữ cái đầu làm avatar
$getInitials = function ($name) {
    $name = trim(preg_replace('/^(Mr\.|Ms\.|Bác sĩ|BS\.|ThS\.|TS\.)\s+/iu', '', (string) $name));
    if ($name === '') return 'TK';
    $words = preg_split('/\s+/u', $name);
    if (count($words) >= 2) {
        $first = mb_substr($words[0], 0, 1, 'UTF-8');
        $last = mb_substr(end($words), 0, 1, 'UTF-8');
        return mb_strtoupper($first . $last, 'UTF-8');
    }
    return mb_strtoupper(mb_substr($name, 0, 2, 'UTF-8'), 'UTF-8');
};
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'secretary')); ?>

<style>
/* Scoped styles cho trang Ban Thư Ký Đoàn với Background nhận diện thương hiệu */
.sec-page-wrapper {
  background-color: #f1f5f9;
  background-image:
    url('<?php echo $e($topHeader); ?>'),
    radial-gradient(circle at 10% 8%, rgba(29, 78, 216, 0.09) 0%, transparent 45%),
    radial-gradient(circle at 90% 18%, rgba(245, 158, 11, 0.1) 0%, transparent 40%),
    radial-gradient(circle at 50% 85%, rgba(13, 148, 136, 0.06) 0%, transparent 45%),
    radial-gradient(rgba(148, 163, 184, 0.35) 1.2px, transparent 1.2px);
  background-repeat: no-repeat, no-repeat, no-repeat, no-repeat, repeat;
  background-position: top center, top left, top right, bottom center, 0 0;
  background-size: 100% 280px, auto, auto, auto, 24px 24px;
  min-height: calc(100vh - 65px);
  position: relative;
}

.sec-hero-gradient {
  background: 
    linear-gradient(135deg, rgba(11, 19, 41, 0.94) 0%, rgba(23, 52, 121, 0.9) 55%, rgba(29, 78, 216, 0.85) 100%),
    url('<?php echo $e($topImage); ?>') center / cover no-repeat;
  position: relative;
  overflow: hidden;
  box-shadow: 0 20px 40px -15px rgba(11, 19, 41, 0.4);
}
.sec-hero-gradient::before {
  content: "";
  position: absolute;
  top: -40%;
  right: -10%;
  width: 480px;
  height: 480px;
  background: radial-gradient(circle, rgba(245, 158, 11, 0.28) 0%, rgba(245, 158, 11, 0) 70%);
  border-radius: 50%;
  pointer-events: none;
}
.sec-hero-gradient::after {
  content: "";
  position: absolute;
  bottom: -30%;
  left: 5%;
  width: 360px;
  height: 360px;
  background: radial-gradient(circle, rgba(37, 99, 235, 0.35) 0%, rgba(37, 99, 235, 0) 70%);
  border-radius: 50%;
  pointer-events: none;
}

/* Card tổng thể */
.sec-card {
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  border: 1px solid #e2e8f0;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
}
.sec-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.12), 0 10px 16px -6px rgba(15, 23, 42, 0.06) !important;
  border-color: #cbd5e1;
}

/* Header của từng thẻ card: Deep Navy & Royal Blue với điểm nhấn vàng kim */
.sec-card-header {
  background: linear-gradient(135deg, #0b1329 0%, #173878 52%, #1e40af 100%);
  color: #ffffff;
  padding: 16px 22px;
  position: relative;
  overflow: hidden;
  border-top-left-radius: 19px;
  border-top-right-radius: 19px;
}
.sec-card-header::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 35%, #60a5fa 70%, #38bdf8 100%);
}
.sec-card-header::after {
  content: "";
  position: absolute;
  right: -15px;
  bottom: -15px;
  width: 120px;
  height: 120px;
  background: radial-gradient(circle, rgba(245, 158, 11, 0.25) 0%, transparent 70%);
  pointer-events: none;
}

.sec-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  color: #1d4ed8;
  font-weight: 700;
  font-size: 15px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #ffffff;
  box-shadow: 0 2px 8px rgba(29, 78, 216, 0.18);
  flex-shrink: 0;
}

.sec-member-row {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid #e2e8f0;
  border-left: 3.5px solid #1d4ed8 !important;
  border-radius: 14px;
  padding: 11px 14px;
  transition: all 0.22s ease;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
}
.sec-member-row:hover {
  background: #ffffff;
  border-color: #cbd5e1;
  border-left-color: #2563eb !important;
  transform: translateX(2px);
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.07);
}

.sec-unit-pill {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 9px 12px;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
}
.sec-unit-pill:hover {
  background: #eff6ff;
  border-color: #93c5fd;
  color: #1d4ed8;
  transform: translateX(3px);
  box-shadow: 0 4px 10px rgba(29, 78, 216, 0.08);
}

.sec-call-btn {
  background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
  color: #ffffff !important;
  border: none;
  transition: all 0.2s ease;
  box-shadow: 0 4px 10px rgba(29, 78, 216, 0.25);
}
.sec-call-btn:hover {
  background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
  transform: translateY(-1px);
  box-shadow: 0 6px 14px rgba(29, 78, 216, 0.35);
}

.sec-copy-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #64748b;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}
.sec-copy-btn:hover {
  background: #f1f5f9;
  color: #1d4ed8;
  border-color: #cbd5e1;
}

.sec-search-input {
  border: 2px solid #e2e8f0;
  border-radius: 16px;
  padding: 14px 20px 14px 50px;
  font-size: 15px;
  background: #ffffff;
  transition: all 0.25s ease;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
}
.sec-search-input:focus {
  border-color: #1d4ed8;
  box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.12), 0 4px 16px rgba(15, 23, 42, 0.06);
  outline: none;
}

.sec-filter-chip {
  border-radius: 9999px;
  padding: 6px 14px;
  font-size: 13px;
  font-weight: 600;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  box-shadow: 0 2px 4px rgba(15, 23, 42, 0.02);
}
.sec-filter-chip:hover {
  background: #f1f5f9;
  color: #0f172a;
}
.sec-filter-chip.active {
  background: #1d4ed8;
  border-color: #1d4ed8;
  color: #ffffff;
  box-shadow: 0 4px 10px rgba(29, 78, 216, 0.25);
}

.sec-hero-pill {
  background: rgba(255, 255, 255, 0.15) !important;
  border: 1px solid rgba(255, 255, 255, 0.28) !important;
  color: #ffffff !important;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border-radius: 12px;
  padding: 8px 14px;
}

.sec-hero-badge {
  background: rgba(255, 255, 255, 0.15) !important;
  border: 1px solid rgba(245, 158, 11, 0.4) !important;
  color: #fef08a !important;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.sec-hero-card-right {
  background: rgba(255, 255, 255, 0.12) !important;
  border: 1px solid rgba(255, 255, 255, 0.22) !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  color: #ffffff !important;
}

.sec-breadcrumb {
  color: rgba(255, 255, 255, 0.9);
}
.sec-breadcrumb a {
  color: rgba(255, 255, 255, 0.85) !important;
  text-decoration: none;
}
.sec-breadcrumb a:hover {
  color: #ffffff !important;
}
.sec-breadcrumb-current {
  color: #ffffff !important;
  font-weight: 700;
}

mark.sec-hl {
  background: #fef08a;
  color: #0f172a;
  padding: 1px 4px;
  border-radius: 4px;
  font-weight: 600;
}
</style>

<main class="flex-grow-1 sec-page-wrapper py-4 py-lg-5">
  <div class="container position-relative" style="z-index: 2;">

    <!-- Breadcrumb -->
    <nav class="mb-3 small d-flex align-items-center gap-1.5 sec-breadcrumb" aria-label="Breadcrumb">
      <a href="<?php echo $e($base); ?>/" class="d-inline-flex align-items-center gap-1">
        <span class="material-symbols-outlined fs-6">home</span> Trang chủ
      </a>
      <span class="opacity-75">/</span>
      <span class="sec-breadcrumb-current">Ban Thư Ký Đoàn</span>
    </nav>

    <!-- Hero Banner Card -->
    <div class="card border-0 rounded-4 shadow-sm sec-hero-gradient text-white mb-4 mb-lg-5 p-4 p-md-5">
      <div class="row align-items-center gy-4 position-relative" style="z-index: 1;">
        <div class="col-12 col-lg-8">
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill sec-hero-badge text-xs fw-bold text-uppercase tracking-wider mb-3">
            <span class="material-symbols-outlined fs-6 text-amber-300">verified</span>
            Ban Tổ Chức Đại Hội Mường Thanh 2026
          </div>
          <h1 class="fw-black fs-2 fs-md-1 mb-2 text-white text-uppercase" style="letter-spacing: -0.02em;">
            Danh Sách Ban Thư Ký Đoàn Phụ Trách
          </h1>
          <p class="text-white text-opacity-85 fs-6 mb-4 lh-base" style="max-width: 680px;">
            Đầu mối kết nối trực tiếp, đồng hành hỗ trợ Quý Đại biểu thuộc từng đơn vị thành viên, tiếp nhận thông tin hậu cần và điều phối ẩm thực trong suốt thời gian diễn ra Đại hội.
          </p>
          <div class="d-flex align-items-center gap-2 gap-md-3 flex-wrap">
            <div class="d-inline-flex align-items-center gap-2 sec-hero-pill text-sm">
              <span class="material-symbols-outlined fs-5 text-amber-300">groups</span>
              <span><strong><?php echo $totalGroups; ?></strong> Tổ Thư ký</span>
            </div>
            <div class="d-inline-flex align-items-center gap-2 sec-hero-pill text-sm">
              <span class="material-symbols-outlined fs-5 text-amber-300">badge</span>
              <span><strong><?php echo $totalMembers; ?></strong> Cán bộ phụ trách</span>
            </div>
            <div class="d-inline-flex align-items-center gap-2 sec-hero-pill text-sm">
              <span class="material-symbols-outlined fs-5 text-amber-300">domain</span>
              <span><strong><?php echo $totalUnits; ?></strong> Đơn vị thành viên</span>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4 text-lg-end d-none d-lg-block">
          <div class="d-inline-flex flex-column align-items-end p-4 rounded-4 sec-hero-card-right">
            <span class="material-symbols-outlined mb-2 text-amber-300" style="font-size: 42px;">support_agent</span>
            <span class="text-uppercase text-white text-opacity-75 small fw-bold">Tổng đài hỗ trợ sự kiện</span>
            <span class="fs-5 fw-bold text-white mt-1">Trực 24/24 Giờ</span>
            <span class="text-white text-opacity-75 small mt-1">Hỗ trợ tiếp nhận thông tin kịp thời</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Notice Box: Quy định suất ăn 24 giờ -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 mb-lg-5 overflow-hidden" style="background: linear-gradient(to right, #fffbeb 0%, #fef3c7 100%); border-left: 5px solid #f59e0b !important;">
      <div class="p-3 p-md-4 d-flex align-items-start gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.22); color: #d97706; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);">
          <span class="material-symbols-outlined fs-4">restaurant_menu</span>
        </div>
        <div class="flex-grow-1">
          <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
            <span class="badge bg-amber-500 text-white fw-bold px-2.5 py-1 text-xs rounded-pill" style="background:#f59e0b;">LƯU Ý QUAN TRỌNG VỀ SUẤT ĂN</span>
            <span class="text-secondary small">Trích quy định Sổ tay Đại hội</span>
          </div>
          <p class="text-slate-800 small mb-1 lh-base">
            Trường hợp đoàn không dùng bữa theo lịch trình, <strong>Trưởng đoàn vui lòng báo hủy suất ăn cho Ban Thư Ký Đoàn phụ trách trước ít nhất 24 giờ</strong> so với giờ ăn để Ban Tổ chức và bộ phận Bếp kịp thời điều chỉnh thực đơn chu đáo, đảm bảo chất lượng phục vụ và tránh lãng phí.
          </p>
          <div class="text-secondary small mt-1 d-flex align-items-center gap-1">
            <span class="material-symbols-outlined fs-6 text-amber-600">info</span>
            <span>Mọi thay đổi phát sinh đột xuất, Quý Trưởng đoàn vui lòng gọi điện thoại trực tiếp cho Thư ký phụ trách bên dưới.</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Live Search & Filter Bar -->
    <div class="card border-0 rounded-4 shadow-sm bg-white p-3 p-md-4 mb-4" style="border: 1px solid rgba(226, 232, 240, 0.9) !important;">
      <div class="row align-items-center g-3">
        <div class="col-12 col-md-7 col-lg-8">
          <div class="position-relative">
            <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary" style="font-size: 22px;">search</span>
            <input
              type="text"
              id="secretarySearch"
              class="form-control sec-search-input w-100"
              placeholder="Tìm nhanh theo tên đơn vị (VD: Ninh Bình, Sài Gòn, Cienco5...), tên Thư ký hoặc SĐT..."
              autocomplete="off"
            />
            <button
              type="button"
              id="clearSearchBtn"
              class="btn btn-sm btn-link position-absolute top-50 end-0 translate-middle-y me-2 text-secondary text-decoration-none p-1 d-none"
              title="Xóa tìm kiếm"
            >
              <span class="material-symbols-outlined fs-5">close</span>
            </button>
          </div>
        </div>

        <div class="col-12 col-md-5 col-lg-4 text-md-end">
          <div class="d-inline-flex align-items-center gap-2 text-secondary small bg-slate-50 px-3 py-2 rounded-pill border">
            <span class="material-symbols-outlined fs-5 text-primary">filter_list</span>
            <span>Hiển thị <strong id="counterGroups" class="text-dark"><?php echo $totalGroups; ?></strong>/<?php echo $totalGroups; ?> tổ (<strong id="counterUnits" class="text-dark"><?php echo $totalUnits; ?></strong> đơn vị)</span>
          </div>
        </div>

        <!-- Quick filter chips -->
        <div class="col-12 pt-1">
          <div class="d-flex align-items-center gap-2 overflow-x-auto pb-1" id="filterChips">
            <span class="text-secondary small fw-semibold flex-shrink-0 me-1">Gợi ý tìm nhanh:</span>
            <button type="button" class="sec-filter-chip active" data-filter="">Tất cả</button>
            <button type="button" class="sec-filter-chip" data-filter="Ninh Bình">Ninh Bình</button>
            <button type="button" class="sec-filter-chip" data-filter="Hà Nội">Hà Nội</button>
            <button type="button" class="sec-filter-chip" data-filter="Diễn Lâm">Diễn Lâm</button>
            <button type="button" class="sec-filter-chip" data-filter="Đà Nẵng">Đà Nẵng</button>
            <button type="button" class="sec-filter-chip" data-filter="Sài Gòn">Sài Gòn</button>
            <button type="button" class="sec-filter-chip" data-filter="Hạ Long">Hạ Long</button>
            <button type="button" class="sec-filter-chip" data-filter="Cienco5">Cienco5</button>
            <button type="button" class="sec-filter-chip" data-filter="Siêu thị">Siêu thị</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State khi không có dữ liệu gốc -->
    <?php if (empty($secretaries)): ?>
      <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white my-4">
        <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center bg-slate-100 text-secondary" style="width: 72px; height: 72px;">
          <span class="material-symbols-outlined" style="font-size: 36px;">groups</span>
        </div>
        <h3 class="fw-bold text-dark fs-5 mb-2">Đang cập nhật danh sách Ban Thư Ký</h3>
        <p class="text-secondary small mb-0" style="max-width: 480px; margin: 0 auto;">
          Ban Tổ chức đang hoàn tất phân công các tổ thư ký đoàn. Vui lòng quay lại sau hoặc liên hệ Hotline Đại hội để được trợ giúp.
        </p>
      </div>
    <?php else: ?>

      <!-- Grid Cards Danh sách Ban Thư Ký Đoàn -->
      <div class="row g-4" id="secretaryGrid">
        <?php foreach ($secretaries as $idx => $g):
          $groupNum = $idx + 1;
          $groupName = isset($g['group_name']) && $g['group_name'] !== ''
            ? $g['group_name']
            : (isset($g['title']) && $g['title'] !== '' ? $g['title'] : 'Tổ Thư ký ' . str_pad((string)$groupNum, 2, '0', STR_PAD_LEFT));

          $members = isset($g['members']) && is_array($g['members']) ? $g['members'] : array();
          $units = isset($g['units']) && is_array($g['units']) ? $g['units'] : array();
          $unitCount = count($units);
        ?>
        <div class="col-12 col-xl-6 sec-card-col" data-group-index="<?php echo $idx; ?>">
          <div class="card rounded-4 shadow-sm h-100 sec-card overflow-hidden d-flex flex-column">

            <!-- Card Header: Deep Navy & Royal Blue với điểm nhấn vàng kim sang trọng -->
            <div class="sec-card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
              <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill fw-bold text-uppercase px-3 py-1.5" style="background: rgba(255, 255, 255, 0.18); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.28); font-size: 13px; letter-spacing: 0.04em; backdrop-filter: blur(4px);">
                  <span class="material-symbols-outlined fs-6 text-amber-300 align-text-bottom me-1">groups</span>
                  <?php echo $e($groupName); ?>
                </span>
                <span class="small fw-medium d-none d-sm-inline" style="color: rgba(255, 255, 255, 0.85);">
                  • <?php echo count($members); ?> cán bộ phụ trách
                </span>
              </div>
              <span class="badge rounded-pill px-3 py-1.5 text-xs fw-semibold" style="background: rgba(245, 158, 11, 0.22); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); backdrop-filter: blur(4px);">
                <span class="group-unit-count fw-bold"><?php echo $unitCount; ?></span> đơn vị phụ trách
              </span>
            </div>

            <!-- Members list (Cán bộ Thư ký đoàn) -->
            <div class="p-3 p-md-4 border-bottom" style="background: #f8fafc;">
              <span class="text-uppercase text-secondary fw-bold text-xs d-block mb-3" style="letter-spacing: 0.05em;">
                <span class="material-symbols-outlined fs-6 align-text-bottom text-primary">person</span>
                Cán bộ Thư ký phụ trách
              </span>

              <div class="d-flex flex-column gap-2.5">
                <?php foreach ($members as $m):
                  $rawName = isset($m['name']) ? $m['name'] : '';
                  $rawPhone = isset($m['phone']) ? $m['phone'] : '';
                  $cleanPhone = str_replace(array(' ', '.', '-', '(', ')'), '', $rawPhone);
                  $initials = $getInitials($rawName);
                  $role = isset($m['role']) && $m['role'] !== '' ? $m['role'] : 'Thư ký đoàn';
                ?>
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap sec-member-row">
                  <div class="d-flex align-items-center gap-2.5">
                    <div class="sec-avatar"><?php echo $e($initials); ?></div>
                    <div>
                      <div class="fw-bold text-dark fs-6 sec-member-name"><?php echo $e($rawName); ?></div>
                      <span class="text-muted small text-xs"><?php echo $e($role); ?></span>
                    </div>
                  </div>

                  <?php if (!empty($rawPhone)): ?>
                    <div class="d-flex align-items-center gap-1.5">
                      <a
                        href="tel:<?php echo $e($cleanPhone); ?>"
                        class="btn btn-sm sec-call-btn rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1 text-xs fw-bold"
                        title="Gọi điện trực tiếp"
                      >
                        <span class="material-symbols-outlined fs-6">call</span>
                        <span class="sec-phone-text"><?php echo $e($rawPhone); ?></span>
                      </a>
                      <button
                        type="button"
                        class="sec-copy-btn btn-copy-phone"
                        data-phone="<?php echo $e($cleanPhone); ?>"
                        title="Sao chép số điện thoại"
                      >
                        <span class="material-symbols-outlined fs-6">content_copy</span>
                      </button>
                    </div>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Units list (Đơn vị phụ trách) -->
            <div class="p-3 p-md-4 flex-grow-1 d-flex flex-column bg-white">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-uppercase text-secondary fw-bold text-xs" style="letter-spacing: 0.05em;">
                  <span class="material-symbols-outlined fs-6 align-text-bottom text-primary">domain</span>
                  Danh sách đơn vị phân công
                </span>
                <span class="text-muted small text-xs">(Báo hủy suất ăn trước 24h)</span>
              </div>

              <?php if (empty($units)): ?>
                <div class="text-muted small fst-italic py-2 text-center">— Đang cập nhật đơn vị —</div>
              <?php else: ?>
                <div class="sec-units-container d-flex flex-column gap-2">
                  <?php foreach ($units as $uIdx => $u):
                    $unitName = is_array($u) ? (isset($u['name']) ? $u['name'] : '') : (string) $u;
                    $isHotel = (mb_stripos($unitName, 'Mường Thanh', 0, 'UTF-8') !== false);
                    $isSupermarket = (mb_stripos($unitName, 'Siêu thị', 0, 'UTF-8') !== false);
                    $isCienco = (mb_stripos($unitName, 'Cienco', 0, 'UTF-8') !== false || mb_stripos($unitName, 'Địa ốc', 0, 'UTF-8') !== false);
                    $icon = $isSupermarket ? 'storefront' : ($isCienco ? 'apartment' : ($isHotel ? 'hotel' : 'business'));
                  ?>
                  <div class="sec-unit-pill d-flex align-items-center gap-2.5 sec-unit-item">
                    <span class="badge bg-slate-200 text-slate-700 rounded-pill text-xs px-2 py-0.5 fw-semibold flex-shrink-0" style="font-size: 11px;">
                      <?php echo str_pad((string)($uIdx + 1), 2, '0', STR_PAD_LEFT); ?>
                    </span>
                    <span class="material-symbols-outlined fs-6 text-primary flex-shrink-0"><?php echo $icon; ?></span>
                    <span class="text-slate-800 small fw-medium sec-unit-name lh-sm"><?php echo $e($unitName); ?></span>
                  </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Card Footer: Quick Reminder -->
            <div class="px-4 py-2.5 bg-slate-50 border-top mt-auto d-flex align-items-center justify-content-between text-muted text-xs">
              <span class="d-inline-flex align-items-center gap-1">
                <span class="material-symbols-outlined fs-6 text-warning">alarm</span> Báo hủy suất ăn trước 24h
              </span>
              <span class="d-inline-flex align-items-center gap-1">
                <span class="material-symbols-outlined fs-6 text-success">check_circle</span> Trực hỗ trợ đoàn
              </span>
            </div>

          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- No Search Results Found Alert -->
      <div id="noSearchResults" class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white my-4 d-none">
        <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center bg-slate-100 text-secondary" style="width: 64px; height: 64px;">
          <span class="material-symbols-outlined" style="font-size: 32px;">search_off</span>
        </div>
        <h3 class="fw-bold text-dark fs-5 mb-2">Không tìm thấy kết quả phù hợp</h3>
        <p class="text-secondary small mb-3" style="max-width: 440px; margin: 0 auto;">
          Không tìm thấy đơn vị hoặc thư ký nào khớp với từ khóa "<strong id="searchQueryDisplay"></strong>". Quý vị vui lòng thử lại với từ khóa khác.
        </p>
        <div>
          <button type="button" id="resetSearchBtn" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold text-sm d-inline-flex align-items-center gap-1">
            <span class="material-symbols-outlined fs-6">refresh</span> Xem tất cả danh sách
          </button>
        </div>
      </div>

    <?php endif; ?>

    <!-- Bottom Quick Links Callout -->
    <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mt-5 bg-white" style="border-top: 4px solid #f59e0b !important;">
      <div class="row align-items-center gy-3">
        <div class="col-12 col-md-8">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-amber-500 fs-4">emergency</span>
            <h2 class="fw-bold text-dark fs-5 mb-0">Cần hỗ trợ về Y tế, An ninh hoặc các dịch vụ khác?</h2>
          </div>
          <p class="text-secondary small mb-0 lh-base">
            Ban Tổ chức bố trí tổ trực Y tế 24/24, xe cứu thương thường trực và đội phản ứng nhanh An ninh - PCCC tại Khách sạn Mường Thanh Luxury Ninh Bình.
          </p>
        </div>
        <div class="col-12 col-md-4 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
          <a href="<?php echo $e($base); ?>/ho-tro" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold text-sm d-inline-flex align-items-center gap-1">
            <span class="material-symbols-outlined fs-6">support_agent</span> Đầu mối hỗ trợ
          </a>
          <a href="<?php echo $e($base); ?>/agenda-tong-the" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold text-sm d-inline-flex align-items-center gap-1">
            <span class="material-symbols-outlined fs-6">calendar_month</span> Lịch trình tổng thể
          </a>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- Toast thông báo sao chép -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
  <div id="copyToast" class="toast align-items-center text-white bg-dark border-0 rounded-pill shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex px-3 py-2">
      <div class="toast-body d-flex align-items-center gap-1.5 py-0 text-sm">
        <span class="material-symbols-outlined fs-6 text-success">check_circle</span>
        <span>Đã sao chép số điện thoại!</span>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  // Hàm loại bỏ dấu tiếng Việt để tìm kiếm không dấu
  function removeAccents(str) {
    if (!str) return '';
    return str
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd')
      .replace(/Đ/g, 'd')
      .trim();
  }

  var searchInput = document.getElementById('secretarySearch');
  var clearBtn = document.getElementById('clearSearchBtn');
  var filterChips = document.querySelectorAll('.sec-filter-chip');
  var cardCols = document.querySelectorAll('.sec-card-col');
  var noResults = document.getElementById('noSearchResults');
  var queryDisplay = document.getElementById('searchQueryDisplay');
  var resetBtn = document.getElementById('resetSearchBtn');
  var counterGroups = document.getElementById('counterGroups');
  var counterUnits = document.getElementById('counterUnits');
  var totalGroups = cardCols.length;

  // Khởi tạo cache tìm kiếm cho từng card
  var cardData = [];
  cardCols.forEach(function(col, idx) {
    var memberNames = [];
    var phones = [];
    col.querySelectorAll('.sec-member-row').forEach(function(mRow) {
      var nameEl = mRow.querySelector('.sec-member-name');
      var phoneEl = mRow.querySelector('.sec-phone-text');
      if (nameEl) memberNames.push(nameEl.innerText);
      if (phoneEl) phones.push(phoneEl.innerText.replace(/\s+/g, ''));
    });

    var unitItems = [];
    col.querySelectorAll('.sec-unit-item').forEach(function(uItem) {
      var uNameEl = uItem.querySelector('.sec-unit-name');
      if (uNameEl) {
        unitItems.push({
          el: uItem,
          nameEl: uNameEl,
          rawText: uNameEl.innerText,
          normText: removeAccents(uNameEl.innerText)
        });
      }
    });

    var groupBadge = col.querySelector('.badge');
    var groupTitle = groupBadge ? groupBadge.innerText : '';

    var allSearchable = [
      groupTitle,
      memberNames.join(' '),
      phones.join(' '),
      unitItems.map(function(u) { return u.rawText; }).join(' ')
    ].join(' ');

    cardData.push({
      col: col,
      idx: idx,
      normSearchable: removeAccents(allSearchable),
      unitItems: unitItems,
      countBadge: col.querySelector('.group-unit-count')
    });
  });

  function performSearch(query) {
    query = (query || '').trim();
    var normQuery = removeAccents(query);

    if (clearBtn) {
      if (query !== '') {
        clearBtn.classList.remove('d-none');
      } else {
        clearBtn.classList.add('d-none');
      }
    }

    var visibleGroupCount = 0;
    var visibleUnitTotal = 0;

    cardData.forEach(function(data) {
      if (!normQuery) {
        // Hiện toàn bộ
        data.col.classList.remove('d-none');
        data.unitItems.forEach(function(u) {
          u.el.classList.remove('d-none');
          u.nameEl.innerHTML = escapeHtml(u.rawText);
        });
        if (data.countBadge) {
          data.countBadge.innerText = data.unitItems.length;
        }
        visibleGroupCount++;
        visibleUnitTotal += data.unitItems.length;
        return;
      }

      var groupMatches = data.normSearchable.indexOf(normQuery) !== -1;
      if (!groupMatches) {
        data.col.classList.add('d-none');
        return;
      }

      // Card có khớp -> kiểm tra chi tiết các đơn vị
      data.col.classList.remove('d-none');
      visibleGroupCount++;

      var matchedUnitsInCard = 0;
      data.unitItems.forEach(function(u) {
        var isUnitMatch = u.normText.indexOf(normQuery) !== -1;
        if (isUnitMatch) {
          u.el.classList.remove('d-none');
          matchedUnitsInCard++;
          visibleUnitTotal++;
          // Highlight từ khóa
          u.nameEl.innerHTML = highlightKeyword(u.rawText, query);
        } else {
          // Nếu khớp theo tên thư ký hoặc số điện thoại thì vẫn giữ đơn vị,
          // còn nếu đang tìm tên đơn vị thì chỉ hiện đơn vị khớp
          var isUnitQuery = isLikelyUnitQuery(normQuery);
          if (isUnitQuery) {
            u.el.classList.add('d-none');
            u.nameEl.innerHTML = escapeHtml(u.rawText);
          } else {
            u.el.classList.remove('d-none');
            u.nameEl.innerHTML = escapeHtml(u.rawText);
            matchedUnitsInCard++;
            visibleUnitTotal++;
          }
        }
      });

      // Nếu tất cả đơn vị bị ẩn nhưng card khớp theo tên thư ký, hiển thị lại đơn vị
      if (matchedUnitsInCard === 0 && data.unitItems.length > 0) {
        data.unitItems.forEach(function(u) {
          u.el.classList.remove('d-none');
          u.nameEl.innerHTML = escapeHtml(u.rawText);
          matchedUnitsInCard++;
          visibleUnitTotal++;
        });
      }

      if (data.countBadge) {
        data.countBadge.innerText = matchedUnitsInCard;
      }
    });

    // Cập nhật bộ đếm
    if (counterGroups) counterGroups.innerText = visibleGroupCount;
    if (counterUnits) counterUnits.innerText = visibleUnitTotal;

    // Hiển thị trạng thái rỗng
    if (noResults) {
      if (visibleGroupCount === 0) {
        noResults.classList.remove('d-none');
        if (queryDisplay) queryDisplay.innerText = query;
      } else {
        noResults.classList.add('d-none');
      }
    }
  }

  function isLikelyUnitQuery(normQuery) {
    // Nếu gõ số điện thoại (chứa chữ số) hoặc tên người (nguyễn, trần, lê, mr, ms) thì không ẩn đơn vị
    if (/[0-9]/.test(normQuery)) return false;
    if (/^(mr|ms|nguyen|tran|le|hoang|phan|do|vu|bui|dang)\b/.test(normQuery)) return false;
    return true;
  }

  function escapeHtml(str) {
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function highlightKeyword(text, keyword) {
    if (!keyword) return escapeHtml(text);
    var normText = removeAccents(text);
    var normKw = removeAccents(keyword);
    var idx = normText.indexOf(normKw);
    if (idx === -1) return escapeHtml(text);

    var start = text.substring(0, idx);
    var match = text.substring(idx, idx + keyword.length);
    var end = text.substring(idx + keyword.length);

    return escapeHtml(start) + '<mark class="sec-hl">' + escapeHtml(match) + '</mark>' + escapeHtml(end);
  }

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      // Bỏ active ở các filter chip khi người dùng tự gõ
      filterChips.forEach(function(c) { c.classList.remove('active'); });
      performSearch(this.value);
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', function() {
      if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
      }
      filterChips.forEach(function(c) {
        if (c.getAttribute('data-filter') === '') c.classList.add('active');
        else c.classList.remove('active');
      });
      performSearch('');
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', function() {
      if (searchInput) {
        searchInput.value = '';
      }
      filterChips.forEach(function(c) {
        if (c.getAttribute('data-filter') === '') c.classList.add('active');
        else c.classList.remove('active');
      });
      performSearch('');
    });
  }

  // Quick filter chips click handler
  filterChips.forEach(function(chip) {
    chip.addEventListener('click', function() {
      filterChips.forEach(function(c) { c.classList.remove('active'); });
      this.classList.add('active');

      var kw = this.getAttribute('data-filter') || '';
      if (searchInput) {
        searchInput.value = kw;
      }
      performSearch(kw);
    });
  });

  // Sao chép số điện thoại
  var copyButtons = document.querySelectorAll('.btn-copy-phone');
  var toastEl = document.getElementById('copyToast');
  copyButtons.forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var phone = this.getAttribute('data-phone');
      if (!phone) return;

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(phone).then(function() {
          showCopyToast();
        });
      } else {
        // Fallback cho trình duyệt cũ
        var tempInput = document.createElement('input');
        tempInput.value = phone;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showCopyToast();
      }
    });
  });

  function showCopyToast() {
    if (toastEl && window.bootstrap && window.bootstrap.Toast) {
      var toast = new window.bootstrap.Toast(toastEl, { delay: 2000 });
      toast.show();
    } else if (toastEl) {
      toastEl.classList.add('show');
      setTimeout(function() { toastEl.classList.remove('show'); }, 2000);
    }
  }

})();
</script>
