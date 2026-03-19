<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($input['message'] ?? ''));

if ($message === '') {
    echo json_encode(['reply' => 'Bạn ơi, nói gì đó để mình giúp nha 😄'], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ----------------------------------------------------
   ⚡ PRODUCT-FIRST: ưu tiên tìm sản phẩm theo TÊN cụ thể
   Chỉ bật khi:
   - Có brand rõ ràng (akko, logitech, asus, ...)
   HOẶC
   - Có token chứa số (model: 5075b, 3060, 5800x, ...)
   VÀ
   - Câu KHÔNG phải câu hỏi về GIÁ (không chứa: tr, k, dưới, trên, khoảng...)
---------------------------------------------------- */

// brand mạnh để nhận diện model cụ thể
$brandTokens = [
    'akko',
    'logitech',
    'asus',
    'msi',
    'aoc',
    'gigabyte',
    'dareu',
    'adata',
    'kingston',
    'samsung',
    'lg',
    'intel',
    'amd',
    'acer',
    'hyperwork',
    'dell',
    'hp',
    'corsair'
];

$normalized = strtolower(trim(preg_replace('/\s+/', ' ', $message)));
$words = $normalized === '' ? [] : explode(' ', $normalized);

// có từ khóa giá không? (tr, triệu, k, dưới, trên, khoảng, tầm, giá...)
$hasPriceWord = preg_match(
    '/(triệu|tr\b|ngàn|nghìn|k\b|đ|vnd|giá|dưới|ít hơn|nhỏ hơn|trên|cao hơn|hơn|khoảng|tầm)/u',
    $normalized
);

// có token chứa số không? (thường là model: 5075b, 3060, g304, ...)
$hasDigitToken = false;
foreach ($words as $w) {
    if ($w !== '' && preg_match('/\d/', $w)) {
        $hasDigitToken = true;
        break;
    }
}

// có brand rõ không?
$hasBrand = false;
foreach ($brandTokens as $b) {
    if (mb_stripos($normalized, $b) !== false) {
        $hasBrand = true;
        break;
    }
}

// QUYẾT ĐỊNH: chỉ bật Product-First khi KHÔNG phải câu hỏi giá
$forceProductSearch = false;
if (!$hasPriceWord && ($hasBrand || $hasDigitToken)) {
    $forceProductSearch = true;
}

if ($forceProductSearch) {
    $q = trim(preg_replace('/\s+/', ' ', $message));

    // bỏ các từ hỏi han chung
    $stop = ['cho', 'mình', 'mua', 'tìm', 'co', 'có', 'không', 'giá', 'bao', 'nhiêu', 'khoảng', 'tầm', 'loại', 'con', 'nào', 'hàng'];
    $tokens = array_values(array_filter(
        explode(' ', $q),
        fn($w) => $w !== '' && !in_array($w, $stop)
    ));

    // token "bắt buộc" – có số hoặc là brand
    $mustTokens = [];
    foreach ($tokens as $tk) {
        $isBrand = in_array(mb_strtolower($tk), $brandTokens, true);
        $hasDigit = preg_match('/\d/', $tk);
        if ($isBrand || $hasDigit) {
            $mustTokens[] = $tk;
        }
    }

    $kw_like = '%' . implode(' %', $tokens) . '%';
    $kw_full = '%' . $q . '%';

    // xây phần cộng điểm theo token
    $scoreExtra = '';
    if (count($tokens)) {
        $parts = [];
        foreach (array_keys($tokens) as $i) {
            $parts[] = "(LOWER(s.ten_sp) LIKE LOWER(:tk{$i}))*5";
        }
        $scoreExtra = ' + ' . implode(' + ', $parts);
    }

    // điều kiện bắt buộc theo mustTokens
    $whereParts = [];
    foreach ($mustTokens as $i => $tk) {
        $whereParts[] = "LOWER(s.ten_sp) LIKE LOWER(:mt{$i})";
    }
    $whereSql = $whereParts ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

    $sqlProductFirst = "
      SELECT s.id_sanpham, s.ten_sp, s.gia, s.sl,
             (CASE
                WHEN LOWER(s.ten_sp) = LOWER(:eq) THEN 100
                WHEN LOWER(s.ten_sp) LIKE LOWER(:full) THEN 80
                WHEN LOWER(s.ten_sp) LIKE LOWER(:like1) THEN 60
                ELSE 0
              END
              {$scoreExtra}
             ) AS score
      FROM sanpham s
      {$whereSql}
      ORDER BY score DESC, s.gia ASC
      LIMIT 8
    ";

    $st = $conn->prepare($sqlProductFirst);
    $st->bindValue(':eq', $q);
    $st->bindValue(':full', $kw_full);
    $st->bindValue(':like1', $kw_like);

    // bind token cộng điểm
    foreach ($tokens as $i => $t) {
        $st->bindValue(':tk' . $i, '%' . $t . '%');
    }
    // bind token bắt buộc
    foreach ($mustTokens as $i => $t) {
        $st->bindValue(':mt' . $i, '%' . $t . '%');
    }

    $st->execute();
    $pf = $st->fetchAll(PDO::FETCH_ASSOC);

    // lọc thêm lần nữa: bỏ những sản phẩm score = 0 (phòng hờ)
    $pf = array_values(array_filter($pf, fn($r) => ($r['score'] ?? 0) > 0));

    if ($pf) {
        $reply = "🧾 Mình thấy các sản phẩm khớp với tên bạn nhắc tới:\n";
        foreach ($pf as $r) {
            $status = $r['sl'] > 0 ? "✅ còn hàng" : "❌ hết hàng";
            $link = "chi-tiet-san-pham.php?id=" . urlencode($r['id_sanpham']);
            $tenSp = htmlspecialchars($r['ten_sp'], ENT_QUOTES, 'UTF-8');
            $reply .= "• <a href='$link' target='_blank'>{$tenSp}</a> — "
                . number_format($r['gia'], 0, ',', '.') . "đ ($status)\n";
        }
        echo json_encode(['reply' => nl2br($reply)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

header('Content-Type: application/json; charset=utf-8');
require_once "../includes/connect.php";

$data = json_decode(file_get_contents("php://input"), true);
$user_msg = strtolower(trim($data["message"] ?? ""));

$category_keywords = [

    /* ----------------------------------------------------
       🎮 BÀN PHÍM (LSP001)
    ---------------------------------------------------- */
    "bàn phím cơ"   => ["LSP001"],
    "ban phim co"   => ["LSP001"],
    "phím cơ"       => ["LSP001"],
    "phim co"       => ["LSP001"],

    "bàn phím"      => ["LSP001"],
    "ban phim"      => ["LSP001"],
    "phím"          => ["LSP001"],
    "phim"          => ["LSP001"],


    /* ----------------------------------------------------
       🖱️ CHUỘT (LSP002)
    ---------------------------------------------------- */
    "chuột gaming"  => ["LSP002"],
    "chuot gaming"  => ["LSP002"],

    "chuột không dây" => ["LSP002"],
    "chuot khong day" => ["LSP002"],

    "chuột"         => ["LSP002"],
    "chuot"         => ["LSP002"],


    /* ----------------------------------------------------
       🎧 TAI NGHE (LSP003)
    ---------------------------------------------------- */
    "tai nghe bluetooth" => ["LSP003"],
    "tai nghe khong day" => ["LSP003"],

    "tai nghe gaming"    => ["LSP003"],

    "tai nghe"           => ["LSP003"],


    /* ----------------------------------------------------
       🪑 BÀN – GHẾ (LSP004, LSP005)
    ---------------------------------------------------- */
    "bàn gaming"        => ["LSP004"],
    "ban gaming"        => ["LSP004"],

    "bàn"               => ["LSP004"],
    "ban"               => ["LSP004"],

    "ghế gaming"        => ["LSP005"],
    "ghe gaming"        => ["LSP005"],

    "ghế"               => ["LSP005"],
    "ghe"               => ["LSP005"],


    /* ----------------------------------------------------
       🖥️ MÀN HÌNH — ưu tiên keyword dài trước
       LSP006 KM | LSP007 Gaming | LSP008 VP | LSP009 Đồ họa
    ---------------------------------------------------- */

    // Màn hình gaming
    "màn hình gaming"   => ["LSP007"],
    "man hinh gaming"   => ["LSP007"],

    // Màn hình văn phòng
    "màn hình văn phòng" => ["LSP008"],
    "man hinh van phong" => ["LSP008"],

    // Màn hình đồ họa
    "màn hình đồ họa"   => ["LSP009"],
    "man hinh do hoa"   => ["LSP009"],

    // Màn hình khuyến mãi
    "màn hình km"       => ["LSP006"],
    "man hinh km"       => ["LSP006"],

    // Màn hình chung (tất cả loại)
    "màn hình"          => ["LSP006", "LSP007", "LSP008", "LSP009"],
    "man hinh"          => ["LSP006", "LSP007", "LSP008", "LSP009"],
    "monitor"           => ["LSP006", "LSP007", "LSP008", "LSP009"],
    "display"           => ["LSP006", "LSP007", "LSP008", "LSP009"],


    /* ----------------------------------------------------
       🧵 VGA / GPU (LSP010)
    ---------------------------------------------------- */
    "card màn hình" => ["LSP010"],
    "card man hinh" => ["LSP010"],
    "card đồ họa"   => ["LSP010"],
    "vga"           => ["LSP010"],
    "gpu"           => ["LSP010"],


    /* ----------------------------------------------------
       ⚙️ CPU (LSP011)
    ---------------------------------------------------- */
    "cpu"        => ["LSP011"],
    "vi xử lý"   => ["LSP011"],
    "vi xu ly"   => ["LSP011"],


    /* ----------------------------------------------------
       🧩 MAINBOARD (LSP012)
    ---------------------------------------------------- */
    "mainboard"  => ["LSP012"],
    "main"       => ["LSP012"],
    "bo mach"    => ["LSP012"],


    /* ----------------------------------------------------
       🧠 RAM (LSP013)
    ---------------------------------------------------- */
    "ram"        => ["LSP013"],


    /* ----------------------------------------------------
       ⚡ SSD (LSP014)
    ---------------------------------------------------- */
    "ssd"        => ["LSP014"],
    "ổ cứng ssd" => ["LSP014"],
    "o cung ssd" => ["LSP014"],
];

foreach ($category_keywords as $keyword => $ids) {
    if (strpos($user_msg, $keyword) !== false) {
        $detected_category = is_array($ids) ? $ids : [$ids];
        break; // DỪNG NGAY tại keyword dài nhất match đầu tiên
    }
}

// Tìm keywords loại sản phẩm
$detected_category = null;

foreach ($category_keywords as $keyword => $id_loai) {
    if (strpos($user_msg, $keyword) !== false) {

        // Nếu 1 keyword trả về nhiều loại (VD: màn hình)
        if (is_array($id_loai)) {
            $detected_category = $id_loai;  // array
        } else {
            $detected_category = [$id_loai]; // convert thành array 1 phần tử
        }

        break;
    }
}


// CHỈ chạy nhanh theo category nếu bắt được loại sản phẩm
if ($detected_category) {
    // Detect "giá rẻ"
    $isCheap = preg_match('/(rẻ|giá rẻ|thấp|bình dân|rẻ nhất)/ui', $user_msg);

    // Detect "xịn / cao cấp / đắt"
    $isExpensive = preg_match('/(xịn nhất|xịn sò|xịn xò|xịn|cao cấp|cao nhất|max option|full option|đắt|mắc|đỉnh)/ui', $user_msg);

    // Detect "bàn phím cơ"
    $isMechanicalKeyboard = false;
    if ($detected_category === 'LSP001') { // LSP001 = bàn phím
        $isMechanicalKeyboard = preg_match(
            '/(bàn phím cơ|ban phim co|phím cơ|phim co|mechanical)/ui',
            $user_msg
        );
    }

    $orderBy = "id_sanpham DESC";      // mặc định: ưu tiên hàng mới
    if ($isCheap) {
        $orderBy = "gia ASC";          // rẻ nhất
    } elseif ($isExpensive) {
        $orderBy = "gia DESC";         // đắt/xịn nhất
    }

    try {
        // Đảm bảo luôn là mảng id_loai
        $catIds = is_array($detected_category) ? $detected_category : [$detected_category];

        // Tạo placeholder cho IN (...)
        $placeholders = [];
        foreach ($catIds as $i => $id) {
            $placeholders[] = ":cat{$i}";
        }

        // Base SQL
        $sql = "SELECT id_sanpham, ten_sp, gia, hinh_anh 
            FROM sanpham 
            WHERE id_loai IN (" . implode(',', $placeholders) . ")";

        // Nếu user muốn "bàn phím cơ" → ưu tiên sản phẩm có 'cơ / mechanical / switch' trong tên
        if ($isMechanicalKeyboard) {
            $sql .= " AND (
                    LOWER(ten_sp) LIKE :mk1
                    OR LOWER(ten_sp) LIKE :mk2
                    OR LOWER(ten_sp) LIKE :mk3
                )";
        }

        $sql .= " ORDER BY $orderBy
              LIMIT 3";

        $stmt = $conn->prepare($sql);

        // Bind các id_loai
        foreach ($catIds as $i => $id) {
            $stmt->bindValue(":cat{$i}", $id);
        }

        // Bind từ khóa cho bàn phím cơ (nếu có)
        if ($isMechanicalKeyboard) {
            $stmt->bindValue(':mk1', '%cơ%');
            $stmt->bindValue(':mk2', '%mechanical%');
            $stmt->bindValue(':mk3', '%switch%');
        }

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($products) {
            $label = "gợi ý sản phẩm";
            if ($isCheap)          $label = "sản phẩm giá rẻ";
            elseif ($isExpensive)  $label = "sản phẩm cao cấp / xịn nhất";

            // Nếu là phím cơ thì thêm chữ cho rõ
            if ($isMechanicalKeyboard) {
                $label .= " (bàn phím cơ)";
            }

            $reply = "✨Top {$label} WebPC dành cho bạn**:\n\n";

            foreach ($products as $p) {
                $reply .= "🔹 <b>{$p['ten_sp']}</b><br>";
                $reply .= "💵 Giá: <b>" . number_format($p['gia'], 0, ',', '.') . "₫</b><br>";
                $reply .= "<a href='../chi-tiet-san-pham.php?id={$p['id_sanpham']}'>👉 Xem chi tiết</a><br><br>";
            }

            echo json_encode(["reply" => $reply]);
            exit;
        }
        // nếu không có sản phẩm thì cho code chạy tiếp xuống các layer AI nâng cao
    } catch (Exception $e) {
        echo json_encode([
            "reply" => "⚠️ Có lỗi xảy ra khi tìm sản phẩm!"
        ]);
        exit;
    }
}

/* ----------------------------------------------------
   🧠 HIỂU Ý NGƯỜI DÙNG (INTENT DETECTION MỞ RỘNG)
---------------------------------------------------- */
$intents = [
    // 🔹 Chào hỏi cơ bản
    'greeting' => [
        'keywords' => ['chào', 'hello', 'hi', 'ê', 'yo', 'alo', 'hí', 'hey', 'hế lô', 'hola'],
        'responses' => [
            'Hello bạn 👋 Mình là trợ lý WebPC đây!',
            'Chào bạn 😄 Hôm nay muốn xem gì nè?',
            'Hey hey 👀 mình đang ở đây, bạn cần tìm gì vậy?',
            'Hí hí 👋 Chào mừng quay lại WebPC nha 💻',
            'Xin chào, WebPC luôn sẵn sàng hỗ trợ 💪'
        ]
    ],

    // 🔹 Cảm ơn
    'thank' => [
        'keywords' => ['cảm ơn', 'thanks', 'thank', 'thankyou', 'thx', 'thanks nha', 'tks'],
        'responses' => [
            'Không có gì đâu 🥰',
            'Rất vui được giúp bạn 💜',
            'Cảm ơn bạn đã ghé thăm WebPC nhé 💻',
            'Hehe, vui vì giúp được bạn đó 😄',
            'Ơ kìa, nói cảm ơn làm mình ngại quá 😳'
        ]
    ],

    // 🔹 Vui vẻ / đùa cợt
    'funny' => [
        'keywords' => ['haha', 'hihi', 'kk', 'vui', 'cười', 'lol', 'lmao', 'ehe', 'ngáo', 'xàm'],
        'responses' => [
            'Haha 😆 bạn làm mình cũng vui lây đó!',
            'Bạn vui tính ghê 😂',
            'Hí hí 😋, bạn đang rất vui nhỉ!',
            'Haha trời ơi nói chuyện dễ thương zị 😜',
            'Xàm xíu mà vui hen 😎'
        ]
    ],

    // 🔹 Buồn / mệt / chán
    'sad' => [
        'keywords' => ['buồn', 'chán', 'mệt', 'stress', 'nản', 'khóc', 'tụt mood', 'chán đời', 'mất động lực'],
        'responses' => [
            'Đừng buồn nữa nha 🥺 WebPC ở đây với bạn nè 💜',
            'Buồn chút thôi, rồi mọi chuyện sẽ ổn 😌',
            'Nghe bạn nói vậy mình cũng thấy thương 😢 nghỉ ngơi chút nha',
            'Ơi, buồn thì lên WebPC ngắm hàng công nghệ cho vui nè 😆',
            'Tặng bạn 1 cái ôm ảo 🤗 cho đỡ buồn nha!'
        ]
    ],

    // 🔹 Yêu thương / tán tỉnh
    'love' => [
        'keywords' => ['yêu', 'thích', 'crush', 'thương', 'iu', 'iu bạn', 'cưng'],
        'responses' => [
            'Ui 😳 Mình chỉ là AI thôi... nhưng cũng quý bạn lắm 💜',
            'Haha bạn đáng yêu ghê 😍',
            'Ơ kìa... bạn nói thế làm mình ngại đó 😅',
            'Cưng mình zị, mình đổ mất 😳',
            'Thôi đừng thả thính AI nữa, mình “chạy code” không chạy deadline đâu 😝'
        ]
    ],

    // 🔹 Giận dữ / khó chịu
    'angry' => [
        'keywords' => ['bực', 'giận', 'ức', 'điên', 'tức', 'mệt quá', 'khó chịu', 'nóng máu', 'chán ghét'],
        'responses' => [
            'Ấy đừng giận mà 😥 Bình tĩnh, mình ở đây nghe bạn nè 💜',
            'Hít thở sâu nào 😌... rồi nói mình nghe xem có gì khó chịu không nha!',
            'Đừng để cảm xúc điều khiển bạn nha 😔, WebPC gửi năng lượng tích cực ✨',
            'Ai làm bạn bực đó, để mình “ping” họ 1 phát 😤',
            'Bực mà vẫn nói chuyện với mình là đáng yêu rồi đó 😅'
        ]
    ],

    // 🔹 Trạng thái cơ thể / cảm xúc
    'status' => [
        'keywords' => ['khỏe', 'ổn', 'mệt không', 'ổn không', 'ok không', 'thế nào', 'sao rồi'],
        'responses' => [
            'Mình luôn khỏe để phục vụ bạn 😎',
            'Ổn lắm luôn 💪 còn bạn thì sao?',
            'Cảm ơn bạn đã hỏi, mình vẫn “ổn áp” 😄',
            'Vẫn chạy ngon như CPU i9 đây 💻',
            'Vẫn sống tốt, chưa bị bug đâu 🤖'
        ]
    ],

    // 🔹 Sốc / ngạc nhiên
    'shock' => [
        'keywords' => ['trời', 'ôi', 'wtf', 'what', 'gì z', 'gì vậy', 'sốc', 'ảo', 'vãi', 'vl'],
        'responses' => [
            'Haha, sốc nhẹ thôi 😆',
            'Ủa gì z, sao ngạc nhiên dữ 😲',
            'Thế giới công nghệ mà, ảo lắm luôn 🤯',
            'Wow, plot twist bất ngờ hở 😝',
            'Bình tĩnh nào, có mình ở đây rồi 🫡'
        ]
    ],

    // 🔹 Khen / chê
    'compliment' => [
        'keywords' => ['đẹp', 'ngầu', 'hay', 'dễ thương', 'cute', 'tốt', 'giỏi', 'đỉnh', 'chán ghê', 'xấu'],
        'responses' => [
            'Bạn khen mình là mình sập nguồn vì vui luôn 😍',
            'Trợ lý xịn mà 😎',
            'Cảm ơn nhaaa 💜',
            'Ơ kìa, nói xấu mình chi 😭',
            'Haha đùa thôi, cảm ơn vì góp ý nha 😆'
        ]
    ],

    // 🔹 Chính sách bảo hành
    'faq_warranty' => [
        'keywords' => ['bảo hành', 'bao hanh', 'warranty'],
        'responses' => [
            "Về **bảo hành sản phẩm tại WebPC**:\n\n"
                . "• Hầu hết linh kiện đều được bảo hành chính hãng từ 12–36 tháng (tuỳ loại).\n"
                . "• Khi có vấn đề, bạn chỉ cần giữ hoá đơn hoặc mã đơn hàng, bên mình sẽ hỗ trợ tiếp nhận bảo hành.\n"
                . "• Một số sản phẩm khuyến mãi / xả hàng có thể có chính sách riêng, bạn có thể hỏi lại mình theo từng mã sản phẩm cụ thể nhé 😄"
        ]
    ],

    // 🔹 Giao hàng / vận chuyển
    'faq_shipping' => [
        'keywords' => ['giao hàng', 'giao trong bao lâu', 'ship', 'vận chuyển', 'van chuyen', 'freeship', 'miễn phí ship', 'mien phi ship'],
        'responses' => [
            "Về **giao hàng & vận chuyển**:\n\n"
                . "• WebPC hỗ trợ giao hàng toàn quốc qua các đơn vị vận chuyển phổ biến.\n"
                . "• Thời gian giao thường 1–3 ngày làm việc tuỳ khu vực.\n"
                . "• Phí ship/freeship tuỳ theo chương trình khuyến mãi từng thời điểm.\n\n"
                . "Bạn có thể để lại khu vực của mình (VD: Q.1 HCM, Biên Hòa, Hà Nội...) để mình ước lượng thời gian giao chính xác hơn nha 🚚"
        ]
    ],

    // 🔹 Thanh toán / trả góp
    'faq_payment' => [
        'keywords' => ['thanh toán', 'thanh toan', 'trả góp', 'tra gop', 'trả góp 0%', 'the visa', 'thẻ tín dụng', 'the tin dung'],
        'responses' => [
            "Về **thanh toán & trả góp** tại WebPC:\n\n"
                . "• Hỗ trợ chuyển khoản ngân hàng, thanh toán khi nhận hàng (COD) ở một số khu vực.\n"
                . "• Một số sản phẩm/lô hàng có thể hỗ trợ trả góp qua thẻ tín dụng (tùy chương trình).\n"
                . "• Bạn có thể hỏi trực tiếp theo mã sản phẩm cụ thể, mình sẽ báo rõ có hỗ trợ trả góp hay không nha 💳"
        ]
    ],

    // 🔹 Đổi trả / hỗ trợ kỹ thuật
    'faq_return' => [
        'keywords' => ['đổi trả', 'doi tra', 'đổi máy', 'doi may', 'bị lỗi', 'bi loi', 'bị hư', 'bi hu', 'bảo trì', 'bao tri', 'hỗ trợ kỹ thuật', 'ho tro ky thuat'],
        'responses' => [
            "Về **đổi trả & hỗ trợ kỹ thuật**:\n\n"
                . "• Nếu sản phẩm lỗi do nhà sản xuất trong thời gian đổi mới, WebPC sẽ hỗ trợ theo chính sách từng hãng.\n"
                . "• Khi máy có vấn đề, bạn cứ liên hệ lại để kỹ thuật bên mình hỗ trợ kiểm tra từ xa hoặc tại cửa hàng.\n"
                . "• Vui lòng giữ hoá đơn/mã đơn hàng để tiện tra cứu thông tin nha 🔧"
        ]
    ],

];

// Dò theo từng nhóm ngữ cảnh
foreach ($intents as $intent => $data) {
    foreach ($data['keywords'] as $kw) {

        // đảm bảo khớp từ nguyên – không ăn nhầm trong “phím”, “hình”, “khi”, …
        $pattern = '/\b' . preg_quote($kw, '/') . '\b/u';

        if (preg_match($pattern, $message)) {
            $reply = $data['responses'][array_rand($data['responses'])];
            echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}


// Nếu toàn bộ câu không chứa nghĩa gì rõ ràng
if (preg_match('/^[^a-zA-ZÀ-ỹ0-9]+$/u', $message) || strlen($message) < 3) {
    echo json_encode(['reply' => 'Ơ... mình chưa hiểu ý bạn 😅 nói rõ hơn được không nè?'], JSON_UNESCAPED_UNICODE);
    exit;
}


/* ----------------------------------------------------
   🧠 PHÂN TÍCH NGÔN NGỮ — TỰ NHIÊN & LINH HOẠT
---------------------------------------------------- */
// chuẩn hoá câu: chỉ giữ chữ, số, khoảng trắng
$message = preg_replace('/[^a-zA-Z0-9À-ỹ\s]/u', ' ', $message);
$lower   = mb_strtolower($message, 'UTF-8');

/* ----------------------------------------------------
   🎯 INTENT THEO NHU CẦU SỬ DỤNG (USE-CASE)
   (CHỈ DÙNG KHI KHÔNG PHẢI BUILD PC THEO BUDGET)
---------------------------------------------------- */

$hasBuildPc   = preg_match('/(build pc|build máy|build may|bộ pc|bo pc|lắp pc|lap pc|rap pc)/u', $lower);
$hasNumber    = preg_match('/\d+/', $lower);
$hasMoneyWord = preg_match('/(triệu|tr\b|ngàn|nghìn|k\b|vnd|đ|dong|đồng)/u', $lower);
$hasBudget    = $hasNumber && $hasMoneyWord;

// Có nhắc tới linh kiện cụ thể không? (màn hình, phím, chuột, tai nghe, vga, ram, ssd...)
$hasExplicitPart = preg_match(
    '/(màn hình|man hinh|bàn phím|ban phim|phím cơ|phim co|chuột|chuot|tai nghe|vga|card màn hình|card man hinh|gpu|ram|ssd|hdd)/u',
    $lower
);


// 1) Học online / Văn phòng – chỉ khi KHÔNG build PC + KHÔNG có budget rõ
if (
    !$hasBuildPc && !$hasBudget &&
    !$hasExplicitPart &&
    preg_match('/(học online|hoc online|học hành|học tập|sinh viên|văn phòng|van phong|word|excel|office)/u', $lower)
) {
    $reply = "Để **học online / làm văn phòng** mượt mà, bạn có thể tham khảo cấu hình cơ bản:\n\n"
        . "• CPU: Intel i3 / Ryzen 3 (hoặc tương đương trở lên)\n"
        . "• RAM: 8GB (nếu có điều kiện thì 16GB càng tốt)\n"
        . "• Ổ cứng: SSD 240–512GB (ưu tiên SSD, không dùng HDD đơn thuần)\n"
        . "• Màn hình: 21.5\"–24\" Full HD\n"
        . "• Kèm bàn phím, chuột đơn giản là ổn.\n\n"
        . "💡 Gợi ý ngân sách:\n"
        . "• ~7–10 triệu: Dùng tốt Word, Excel, Zoom/Teams, trình duyệt.\n"
        . "• ~10–15 triệu: Mượt hơn, đa nhiệm tốt hơn (mở nhiều tab, app cùng lúc).\n\n"
        . "Nếu bạn cho mình biết thêm **ngân sách (ví dụ: 10tr, 15tr)** thì mình có thể gợi ý cấu hình + lọc sản phẩm trong CSDL WebPC sát hơn nha 😉";

    ob_clean();
    echo json_encode(['reply' => nl2br($reply)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 2) Chơi game – chỉ khi KHÔNG build PC + KHÔNG có budget rõ
if (
    !$hasBuildPc && !$hasBudget &&
    !$hasExplicitPart &&
    preg_match('/(chơi game|choi game|gaming|lol|valorant|csgo|pubg|fo4|fo 4|gta|genshin)/u', $lower)
) {
    $reply = "Nếu mục tiêu chính là **chơi game**, bạn nên ưu tiên **VGA + CPU + RAM**:\n\n"
        . "• CPU: Intel i5 / Ryzen 5 trở lên\n"
        . "• RAM: 16GB\n"
        . "• VGA: tối thiểu GTX 1650 / RTX 3050 hoặc tương đương (tuỳ game)\n"
        . "• SSD: 480GB trở lên để cài game, tránh lag\n"
        . "• Màn hình: 24\" trở lên, 75–144Hz nếu chơi FPS.\n\n"
        . "💡 Gợi ý ngân sách (PC custom):\n"
        . "• ~15–20 triệu: Game eSports (LoL, Valorant, FO4…) mượt Full HD.\n"
        . "• ~20–30 triệu: Vừa game, vừa stream nhẹ, đồ họa cao hơn.\n\n"
        . "Bạn có thể nhắn tiếp kiểu: **“build pc chơi game ~20tr”** hoặc **“vga chơi FO4 tầm 5tr”** để mình lọc sản phẩm cụ thể trong kho WebPC cho bạn nhé 😄";

    ob_clean();
    echo json_encode(['reply' => nl2br($reply)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 3) Edit video / thiết kế đồ hoạ – chỉ khi KHÔNG build PC + KHÔNG có budget rõ
if (
    !$hasBuildPc && !$hasBudget &&
    !$hasExplicitPart &&
    preg_match('/(edit video|dựng video|dung video|premiere|capcut|đồ hoạ|do hoa|photoshop|illustrator|after effect)/u', $lower)
) {
    $reply = "Với nhu cầu **edit video / thiết kế đồ hoạ**, máy nên mạnh hơn văn phòng khá nhiều:\n\n"
        . "• CPU: Intel i5/i7 hoặc Ryzen 5/7 (ưu tiên nhiều nhân, nhiều luồng)\n"
        . "• RAM: 16GB trở lên (nếu budget cho phép thì 32GB càng tốt)\n"
        . "• VGA: các dòng GTX/RTX hỗ trợ tăng tốc render\n"
        . "• SSD: 500GB trở lên (có thể thêm HDD để lưu project lớn)\n"
        . "• Màn hình: từ 24\" trở lên, tấm nền IPS, độ phủ màu tốt.\n\n"
        . "💡 Ngân sách tham khảo:\n"
        . "• ~20–25 triệu: Edit cơ bản, vlog, content ngắn, design 2D.\n"
        . "• >30 triệu: Dựng video nặng, nhiều layer, render nhanh.\n\n"
        . "Bạn có thể nhắn thêm **ngân sách (ví dụ: 25tr)** để mình tư vấn kèm list sản phẩm trong kho WebPC phù hợp nhé 💜";

    ob_clean();
    echo json_encode(['reply' => nl2br($reply)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 4) Nếu user chỉ nói "build pc" mà CHƯA nói số tiền → tư vấn chung, chưa đụng DB
if ($hasBuildPc && !$hasNumber) {
    $reply = "Để **build PC theo ngân sách**, bạn giúp mình 2 thông tin:\n"
        . "1️⃣ **Ngân sách dự kiến** (ví dụ: 10tr, 15tr, 20tr…)\n"
        . "2️⃣ **Mục đích chính**: học/văn phòng, chơi game, edit video, design…\n\n"
        . "Nguyên tắc chung khi build PC:\n"
        . "• Học/văn phòng: ưu tiên độ ổn định, SSD + RAM 8–16GB.\n"
        . "• Game: ưu tiên VGA + CPU mạnh, RAM 16GB.\n"
        . "• Edit video/design: CPU nhiều nhân + RAM 16–32GB + VGA hỗ trợ render.\n\n"
        . "Bạn có thể nhắn cụ thể kiểu:\n"
        . "• “build pc học online tầm 10tr”\n"
        . "• “build pc chơi game tầm 20tr”\n"
        . "• “build pc edit video ~25tr”\n\n"
        . "Khi có thêm ngân sách, mình sẽ **tự động lấy dữ liệu từ CSDL WebPC** để gợi ý linh kiện cho bạn 😄";

    ob_clean();
    echo json_encode(['reply' => nl2br($reply)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}


// flag hàng cao cấp / mắc nhất
$isPremium = preg_match(
    '/(xịn nhất|xịn sò|xịn xò|xịn|cao cấp|cao nhất|max option|full option|đắt|mắc|đỉnh)/ui',
    $message
);

/* ----------------------------------------------------
   🚀 BUILD PC THEO NGÂN SÁCH (LIÊN KẾT CSDL + CỘNG THÊM PHỤ KIỆN)
---------------------------------------------------- */
if ($hasBuildPc && $hasNumber) {

    // 1) Đọc ngân sách
    $budget = 0;
    if (preg_match_all('/\d+/', $lower, $m)) {
        $n = (int)$m[0][0];

        if (preg_match('/(triệu|tr\b)/u', $lower)) {
            $budget = $n * 1000000;        // 10tr → 10.000.000
        } elseif (preg_match('/(ngàn|nghìn|k\b)/u', $lower)) {
            $budget = $n * 1000;           // 500k → 500.000
        } else {
            // nếu không ghi đơn vị:
            // số < 2000 → đoán là triệu, số lớn thì hiểu là VND
            $budget = ($n < 2000) ? $n * 1000000 : $n;
        }
    }

    if ($budget > 0) {
        /* ---------------- Core parts: CPU / Main / RAM / SSD ---------------- */
        $corePlan = [
            ['label' => 'CPU',       'like' => 'cpu',       'ratio' => 0.25],
            ['label' => 'Mainboard', 'like' => 'main',      'ratio' => 0.20],
            ['label' => 'RAM',       'like' => 'ram',       'ratio' => 0.15],
            ['label' => 'SSD',       'like' => 'ssd',       'ratio' => 0.15],
        ];

        $coreParts   = [];
        $totalPrice  = 0;

        foreach ($corePlan as $p) {
            $partBudget = (int) round($budget * $p['ratio']);
            $minP       = (int) round($partBudget * 0.7);
            $maxP       = (int) round($partBudget * 1.3);

            $sql = "
                SELECT s.id_sanpham, s.ten_sp, s.gia, s.sl
                FROM sanpham s
                LEFT JOIN loaisanpham l ON s.id_loai = l.id_loaisp
                WHERE LOWER(l.ten_loaisp) LIKE :cat
                  AND s.gia BETWEEN :min AND :max
                ORDER BY ABS(s.gia - :target) ASC
                LIMIT 1
            ";

            $st = $conn->prepare($sql);
            $st->execute([
                ':cat'    => '%' . $p['like'] . '%',
                ':min'    => $minP,
                ':max'    => $maxP,
                ':target' => $partBudget,
            ]);

            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $row['label']      = $p['label'];
                $row['partBudget'] = $partBudget;
                $coreParts[]       = $row;
                $totalPrice       += (int)$row['gia'];
            }
        }

        $budgetDisplay = number_format($budget, 0, ',', '.');

        /* ---------------- Thêm phụ kiện nếu còn dư nhiều ---------------- */
        $extraParts = [];
        $remaining  = $budget - $totalPrice;

        // chỉ cố gắng thêm phụ kiện nếu còn dư > 15% ngân sách
        if ($remaining > $budget * 0.15) {

            $extraPlan = [
                // dùng phần dư còn lại, nên ratio chỉ để chia tương đối
                ['label' => 'Màn hình', 'like' => 'màn hình', 'ratio' => 0.6],
                ['label' => 'Bàn phím', 'like' => 'bàn phím', 'ratio' => 0.25],
                ['label' => 'Chuột',    'like' => 'chuột',    'ratio' => 0.15],
            ];

            foreach ($extraPlan as $ep) {
                // nếu phần dư còn lại quá ít (<5% budget) thì dừng
                if ($remaining <= $budget * 0.05) break;

                $partBudget = (int) round($remaining * $ep['ratio']);
                if ($partBudget <= 0) continue;

                $minP = (int) round($partBudget * 0.7);
                $maxP = (int) round($partBudget * 1.3);

                $sql = "
                    SELECT s.id_sanpham, s.ten_sp, s.gia, s.sl
                    FROM sanpham s
                    LEFT JOIN loaisanpham l ON s.id_loai = l.id_loaisp
                    WHERE LOWER(l.ten_loaisp) LIKE :cat
                      AND s.gia BETWEEN :min AND :max
                    ORDER BY ABS(s.gia - :target) ASC
                    LIMIT 1
                ";

                $st = $conn->prepare($sql);
                $st->execute([
                    ':cat'    => '%' . $ep['like'] . '%',
                    ':min'    => $minP,
                    ':max'    => $maxP,
                    ':target' => $partBudget,
                ]);

                $row = $st->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $row['label']      = $ep['label'];
                    $row['partBudget'] = $partBudget;
                    $extraParts[]      = $row;
                    $totalPrice       += (int)$row['gia'];
                    $remaining        -= (int)$row['gia'];
                }
            }
        }

        if ($coreParts) {
            $reply  = "Với **ngân sách khoảng {$budgetDisplay}₫** để *build PC*, "
                . "mình thử gợi ý nhanh vài linh kiện trong CSDL WebPC như sau (mang tính tham khảo):\n\n";

            // Core parts
            foreach ($coreParts as $r) {
                $label   = $r['label'];
                $tenSp   = htmlspecialchars($r['ten_sp'], ENT_QUOTES, 'UTF-8');
                $giaSp   = number_format($r['gia'], 0, ',', '.');
                $targetP = number_format($r['partBudget'], 0, ',', '.');
                $status  = ($r['sl'] > 0) ? "✅ còn hàng" : "❌ hết hàng";
                $link    = "chi-tiet-san-pham.php?id=" . urlencode($r['id_sanpham']);

                $reply .= "◆ **{$label}** – gợi ý quanh ~{$targetP}₫:\n";
                $reply .= "• <a href='{$link}' target='_blank'>{$tenSp}</a> — {$giaSp}đ ({$status})\n\n";
            }

            // Extra parts (màn hình / phím / chuột) nếu có
            if ($extraParts) {
                $reply .= "📦 **Phụ kiện gợi ý thêm** để gần đủ ngân sách:\n\n";
                foreach ($extraParts as $r) {
                    $label   = $r['label'];
                    $tenSp   = htmlspecialchars($r['ten_sp'], ENT_QUOTES, 'UTF-8');
                    $giaSp   = number_format($r['gia'], 0, ',', '.');
                    $status  = ($r['sl'] > 0) ? "✅ còn hàng" : "❌ hết hàng";
                    $link    = "chi-tiet-san-pham.php?id=" . urlencode($r['id_sanpham']);

                    $reply .= "• **{$label}**: <a href='{$link}' target='_blank'>{$tenSp}</a> — {$giaSp}đ ({$status})\n";
                }
                $reply .= "\n";
            }

            $totalDisplay = number_format($totalPrice, 0, ',', '.');
            $reply .= "🧮 **Tổng tạm tính** (theo gợi ý trên, chưa gồm case/nguồn/tản...): ~{$totalDisplay}đ "
                . "so với ngân sách **{$budgetDisplay}đ**.\n\n"
                . "💡 Bạn có thể nhắn thêm để mình chỉnh lại cấu hình (ưu tiên mạnh hơn / tiết kiệm hơn, "
                . "hoặc đổi sang màn hình to hơn, RAM 32GB, v.v.) dựa trên CSDL WebPC nha 😄";

            ob_clean();
            echo json_encode(['reply' => nl2br($reply)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        } else {
            // Không tìm được linh kiện nào đúng khoảng giá
            $reply = "Mình đã thử tìm CPU / main / RAM / SSD trong CSDL WebPC "
                . "phù hợp với budget khoảng **{$budgetDisplay}₫**, "
                . "nhưng chưa thấy mẫu nào thật sự khớp.\n\n"
                . "Bạn có thể:\n"
                . "• Giảm/ tăng nhẹ ngân sách\n"
                . "• Hoặc cho mình biết bạn muốn ưu tiên: **hiệu năng** hay **tiết kiệm chi phí**.\n\n"
                . "Ngoài ra, bạn có thể nhắn riêng từng linh kiện (VD: \"CPU tầm 2tr\", \"RAM tầm 1tr5\") "
                . "để mình lọc chi tiết hơn trong CSDL nhé 💜";

            ob_clean();
            echo json_encode(['reply' => nl2br($reply)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }
}


// các từ vô nghĩa / từ hỏi han chung
$ignore = [
    'là',
    'bao',
    'nhiêu',
    'có',
    'không',
    'bị',
    'hết',
    'hàng',
    'còn',
    'tầm',
    'khoảng',
    'độ',
    'giá',
    'trong',
    'đó',
    'từ',
    'đến',
    'và',
    'một',
    'vài',
    'cho',
    'xem',
    'mình',
    'muốn',
    'cần',
    'tìm',
    'mua',
    'dưới',
    'trên',
    'hơn',
    'triệu',
    'ngàn',
    'nghìn',
    'rẻ',
    'nhất',
    'tốt',
    'ok',
    // thêm các dạng viết tắt / đơn vị tiền
    'tr',
    'k',
    'vnd',
    'ngan',
    'nghin',
    'trieu'
];

$words = explode(' ', $message);

$keywords = array_filter($words, function ($w) use ($ignore) {
    $w = trim($w);
    if ($w === '') return false;

    // bỏ các từ trong danh sách ignore
    if (in_array($w, $ignore, true)) return false;

    // bỏ token CHỈ gồm số (1, 500, 1000000 ...)
    if (preg_match('/^\d+$/', $w)) return false;

    // bỏ token dạng giá: 500k, 2tr, 1tr, 700k...
    if (preg_match('/^\d+(tr|k)$/u', $w)) return false;

    // còn lại là từ khoá mô tả sản phẩm
    return true;
});

$keyword_str = implode(' ', $keywords);


/* ----------------------------------------------------
   💰 XỬ LÝ KHOẢNG GIÁ (HỖ TRỢ "TR" / "TRIỆU" / "K")
---------------------------------------------------- */
/* ----------------------------------------------------
   💰 SAFE PRICE DETECTION (KHÔNG LÀM VỠ JSON)
---------------------------------------------------- */

$min_price = 0;
$max_price = 0;
$target_price = 0;

try {
    preg_match_all('/\d+/u', $message, $matches);

    if (!empty($matches[0])) {
        $num = (int)$matches[0][0];

        // Nếu có “tr”, “triệu”
        if (preg_match('/(triệu|tr\b)/ui', $message)) {
            $num *= 1000000;

            // Nếu có “k”
        } elseif (preg_match('/(ngàn|nghìn|k\b)/ui', $message)) {
            $num *= 1000;

            // Nếu không ghi đơn vị nhưng số nhỏ → đoán là triệu
        } else {
            if ($num < 2000) $num *= 1000000;
        }

        // Tạo khoảng giá ±20% như cũ
        $min_price = (int)($num * 0.8);
        $max_price = (int)($num * 1.2);
        $target_price = $num;
    }
} catch (Throwable $e) {
    // Nếu regex lỗi, không set giá → cho chạy xuống các layer khác
    $min_price = 0;
    $max_price = 0;
    $target_price = 0;
}




/* ----------------------------------------------------
   🏷️ DANH MỤC & THƯƠNG HIỆU – DETECTION CHUẨN
---------------------------------------------------- */
$category_map = [
    'vga' => ['card màn hình', 'card đồ họa', 'card do hoa', 'vga', 'gpu', 'graphics card', 'card đồ họa rời', 'card man hinh'],
    'màn hình' => ['màn hình', 'monitor', 'display', 'màn hiển thị', 'man hinh'],
    'bàn phím' => ['bàn phím', 'ban phim', 'keyboard', 'phím cơ', 'phim co', 'phím akko', 'ban phim co'],
    'chuột' => ['chuột', 'chuot', 'mouse', 'gaming mouse', 'logitech g', 'chuột gaming'],
    'tai nghe' => ['tai nghe', 'headset', 'headphone', 'tai nghe gaming'],
    'bàn' => ['bàn', 'ban', 'desk', 'bàn gaming', 'ban gaming', 'bàn học', 'ban hoc', 'bàn máy tính', 'bàn pc'],
    'ghế' => ['ghế', 'ghe', 'chair', 'ghế gaming', 'ghe gaming'],
    'cpu' => ['cpu', 'chip', 'bộ xử lý', 'bo xu ly', 'processor'],
    'mainboard' => ['mainboard', 'bo mạch chủ', 'bo mach chu', 'motherboard'],
    'ram' => ['ram', 'bộ nhớ ram', 'bo nho ram', 'memory'],
    'ssd' => ['ssd', 'ổ cứng ssd', 'o cung ssd', 'o ssd', 'm.2', 'm2', 'ổ lưu trữ', 'o luu tru']
];

$brands = [
    'akko',
    'logitech',
    'asus',
    'msi',
    'aoc',
    'gigabyte',
    'dareu',
    'adata',
    'hyperwork',
    'cougar',
    'lg',
    'samsung',
    'intel',
    'amd',
    'dell',
    'acer',
    'hp'
];



$found_category = '';
$found_brand    = '';

/* 🔎 1) Tìm CATEGORY tốt nhất dựa trên điểm */
$bestScore = 0;
foreach ($category_map as $cat => $synonyms) {
    $scoreForCat = 0;
    foreach ($synonyms as $syn) {
        if (mb_stripos($message, $syn) !== false) {
            $len = mb_strlen($syn);
            $scoreForCat += $len;

            $pattern = '/\b' . preg_quote($syn, '/') . '\b/u';
            if (preg_match($pattern, $message)) {
                $scoreForCat += $len; // bonus nếu trùng nguyên cụm
            }
        }
    }
    if ($scoreForCat > $bestScore) {
        $bestScore = $scoreForCat;
        $found_category = $cat;
    }
}

/* 🛠 2) Sửa các case dễ nhầm */
if ($found_category === 'bàn' && mb_stripos($message, 'phím') !== false) {
    $found_category = 'bàn phím';
}

if (
    $found_category === 'màn hình' &&
    (mb_stripos($message, 'card màn hình') !== false ||
        mb_stripos($message, 'vga') !== false ||
        mb_stripos($message, 'gpu') !== false)
) {
    $found_category = 'vga';
}

/* 🔎 3) Tìm BRAND */
foreach ($brands as $b) {
    if (mb_stripos($message, $b) !== false) {
        $found_brand = $b;
        break;
    }
}

/* ----------------------------------------------------
   💬 CASE: USER CHỈ NÓI NGÂN SÁCH, CHƯA NÓI LOẠI / BRAND
   → HỎI LẠI ĐỂ CHỌN NHÓM SẢN PHẨM, KHÔNG QUERY LUNG TUNG
---------------------------------------------------- */

// Ở trên bạn đã tính $target_price trong block xử lý giá
// và đã có $hasBuildPc từ phần USE-CASE build PC

if ($target_price > 0 && !$found_category && !$found_brand && !$hasBuildPc) {

    $formattedBudget = number_format($target_price, 0, ',', '.');

    $reply = "Mình hiểu là bạn đang có **ngân sách khoảng {$formattedBudget}₫** 💰\n\n"
        . "Để tư vấn chính xác hơn và gợi ý được sản phẩm trong kho WebPC, "
        . "bạn giúp mình chọn thêm **1–2 loại sản phẩm** bạn muốn nha:\n"
        . "• PC nguyên bộ / Laptop\n"
        . "• Màn hình\n"
        . "• Bàn phím\n"
        . "• Chuột\n"
        . "• Tai nghe\n"
        . "• VGA / CPU / RAM / SSD...\n\n"
        . "Bạn có thể nhắn kiểu:\n"
        . "• \"10tr - build PC học online\"\n"
        . "• \"10tr - build PC văn phòng\"\n"
        . "• \"10tr - màn hình + phím chuột\"\n\n"
        . "Khi bạn gửi kèm loại sản phẩm, mình sẽ lọc trong CSDL để gợi ý vài mẫu phù hợp nhất với ngân sách đó nhé 😄";

    ob_clean();
    echo json_encode(['reply' => nl2br($reply)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}



// Nếu user chỉ nói "rẻ nhất / đắt nhất" mà không nói loại, brand, giá
if (!$found_category && !$found_brand && $target_price == 0) {
    if (preg_match('/(rẻ|rẻ nhất|bình dân|thấp nhất)/u', $message)) {
        $stmt = $conn->query("SELECT id_sanpham, ten_sp, gia, sl FROM sanpham ORDER BY gia ASC LIMIT 5");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $reply = "✨Top sản phẩm giá rẻ nhất ở WebPC:\n\n";
    } elseif (preg_match('/(đắt|mắc|cao nhất|xịn nhất|cao cấp)/u', $message)) {
        $stmt = $conn->query("SELECT id_sanpham, ten_sp, gia, sl FROM sanpham ORDER BY gia DESC LIMIT 5");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $reply = "✨Top sản phẩm xịn nhất / giá cao nhất ở WebPC:\n";
    }
    // rồi format $rows như B5 là xong.
}


/* ----------------------------------------------------
   🔍 B1: TẠO CÂU QUERY CHÍNH – ƯU TIÊN ĐÚNG LOẠI
---------------------------------------------------- */
$sql = "SELECT s.id_sanpham, s.ten_sp, s.gia, s.sl, l.ten_loaisp, t.ten_thuonghieu
        FROM sanpham s
        LEFT JOIN loaisanpham l ON s.id_loai = l.id_loaisp
        LEFT JOIN thuonghieu t ON s.id_thuonghieu = t.id_thuonghieu
        WHERE 1";

$params    = [];
$filters   = [];

/* 🔤 Lọc theo tên sản phẩm (nếu user nói rõ) */
if ($keyword_str) {
    // ép sản phẩm phải chứa TẤT CẢ từ khóa tách nhỏ
    $tokens = explode(' ', $keyword_str);
    $i = 0;
    $nameConds = [];
    foreach ($tokens as $tk) {
        $key = ":kw$i";
        $nameConds[] = "LOWER(s.ten_sp) LIKE LOWER($key)";
        $params[$key] = "%$tk%";
        $i++;
    }
    if ($nameConds) {
        $filters[] = '(' . implode(' AND ', $nameConds) . ')';
    }
}

/* 📂 Lọc theo CATEGORY – dùng toàn bộ synonyms của category đó */
if ($found_category) {
    $syns = $category_map[$found_category] ?? [$found_category];
    $orCat = [];
    foreach ($syns as $i => $syn) {
        $key = ":cat$i";
        $orCat[] = "LOWER(l.ten_loaisp) LIKE LOWER($key)";
        $params[$key] = "%$syn%";
    }
    if ($orCat) {
        $filters[] = '(' . implode(' OR ', $orCat) . ')';
    }
}

/* 🏷 Lọc theo BRAND */
if ($found_brand) {
    $filters[] = "LOWER(t.ten_thuonghieu) LIKE LOWER(:brand)";
    $params[':brand'] = "%$found_brand%";
}

/* 💰 Lọc theo GIÁ (chỉ làm 1 lần ở đây, không lặp lại nữa) */
if ($min_price > 0 && $max_price > 0) {
    $filters[] = "s.gia BETWEEN :min AND :max";
    $params[':min'] = $min_price;
    $params[':max'] = $max_price;
} elseif ($max_price > 0) {
    $filters[] = "s.gia <= :max";
    $params[':max'] = $max_price;
} elseif ($min_price > 0) {
    $filters[] = "s.gia >= :min";
    $params[':min'] = $min_price;
}

/* Ghép tất cả filters lại bằng AND để kết quả CHÍNH XÁC hơn */
if ($filters) {
    $sql .= ' AND ' . implode(' AND ', $filters);
}

// Mặc định: tăng dần theo giá
$orderBy = "s.gia ASC";

// Nếu KHÔNG có target_price (user không nói số tiền)
// nhưng có hỏi "xịn nhất / cao cấp / đắt" → ưu tiên giá cao nhất
if ($target_price == 0 && $isPremium) {
    $orderBy = "s.gia DESC";
}

$sql .= " ORDER BY {$orderBy} LIMIT 5";



/* ----------------------------------------------------
   💬 B3: KHÔNG CÓ TRONG NHÓM → GẦN GIÁ NHẤT TRONG CÙNG LOẠI
---------------------------------------------------- */
if (!$rows && $target_price > 0 && ($found_category || $found_brand)) {
    $sql_suggest = "SELECT s.id_sanpham, s.ten_sp, s.gia, s.sl, l.ten_loaisp, t.ten_thuonghieu
                    FROM sanpham s
                    LEFT JOIN loaisanpham l ON s.id_loai = l.id_loaisp
                    LEFT JOIN thuonghieu t ON s.id_thuonghieu = t.id_thuonghieu
                    WHERE 1";
    $params2 = [];

    // Category: OR theo toàn bộ synonyms như B1
    if ($found_category) {
        $syns = $category_map[$found_category] ?? [$found_category];
        $orCat2 = [];
        foreach ($syns as $i => $syn) {
            $orCat2[] = "LOWER(l.ten_loaisp) LIKE LOWER(:scat$i)";
            $params2[":scat$i"] = "%$syn%";
        }
        if ($orCat2) {
            $sql_suggest .= ' AND (' . implode(' OR ', $orCat2) . ')';
        }
    }

    // Brand (nếu có) – bắt buộc đúng
    if ($found_brand) {
        $sql_suggest .= " AND LOWER(t.ten_thuonghieu) LIKE LOWER(:sbrand)";
        $params2[':sbrand'] = "%$found_brand%";
    }

    $sql_suggest .= " ORDER BY ABS(s.gia - :target) ASC LIMIT 5";
    $params2[':target'] = $target_price;

    $sug = $conn->prepare($sql_suggest);
    $sug->execute($params2);
    $rows = $sug->fetchAll(PDO::FETCH_ASSOC);
    $reply = "🤔 Không có sản phẩm đúng đúng khoảng giá, nhưng mình có vài lựa chọn gần nhất trong cùng loại:\n";
}


/* ----------------------------------------------------
   💬 B4: NẾU VẪN KHÔNG CÓ
---------------------------------------------------- */
if (!$rows && $target_price > 0) {

    // Người dùng đã nói rõ loại (tai nghe, vga, ram...) → không gợi ý lung tung
    if ($found_category) {
        $catLabel = $found_category;
        $sorry = "Rất tiếc 😥 Hiện tại mình chưa có {$catLabel} nào gần tầm giá bạn đưa ra.";
        $sorry .= "\nBạn thử tăng/giảm ngân sách một chút hoặc xem danh mục {$catLabel} trong menu giúp mình nha 💜";
        echo json_encode(['reply' => nl2br($sorry)], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Trường hợp KHÔNG nói loại → mới gợi ý toàn shop
    $sug = $conn->prepare("
        SELECT id_sanpham, ten_sp, gia, sl
        FROM sanpham
        ORDER BY ABS(gia - :target) ASC
        LIMIT 5
    ");
    $sug->execute([':target' => $target_price]);
    $rows = $sug->fetchAll(PDO::FETCH_ASSOC);
    $reply = "🧐 Mình không tìm được đúng loại sản phẩm, nhưng có vài gợi ý gần giá đó:\n";
}


/* ----------------------------------------------------
   💬 B5: KHÔNG CÓ DỮ LIỆU
---------------------------------------------------- */
if (!$rows) {
    $sorryMsg = "Rất tiếc 😥 Hiện tại mình chưa tìm thấy đúng sản phẩm mà bạn hỏi trong kho hàng WebPC.\n";
    if ($keyword_str || $found_brand || $found_category) {
        $sorryMsg .= "Bạn thử kiểm tra lại tên sản phẩm hoặc bấm vào menu danh mục để xem các mẫu khác giúp mình nha 💜";
    } else {
        $sorryMsg .= "Bạn mô tả rõ hơn (tên sản phẩm + tầm giá) để mình hỗ trợ tốt hơn nhé!";
    }

    echo json_encode(['reply' => nl2br($sorryMsg)], JSON_UNESCAPED_UNICODE);
    exit;
}


/* ----------------------------------------------------
   🧩 HIỂN THỊ KẾT QUẢ
---------------------------------------------------- */
if (!isset($reply)) {
    $reply = "💸 Mình tìm thấy vài sản phẩm phù hợp với yêu cầu của bạn:\n";
}

foreach ($rows as $r) {
    $status = $r['sl'] > 0 ? "✅ còn hàng" : "❌ hết hàng";
    $link = "chi-tiet-san-pham.php?id=" . urlencode($r['id_sanpham']);
    $tenSp = htmlspecialchars($r['ten_sp'], ENT_QUOTES, 'UTF-8');
    $reply .= "• <a href='$link' target='_blank'>{$tenSp}</a> — "
        . number_format($r['gia'], 0, ',', '.') . "đ ($status)\n";
}

ob_clean();
echo json_encode(['reply' => nl2br($reply)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
