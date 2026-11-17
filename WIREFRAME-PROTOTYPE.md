# Quy trình và công cụ thiết kế wireframe, prototype

Tài liệu này hướng dẫn thiết kế và bàn giao giao diện cho Shop Laravel bằng Figma. Phạm vi bám theo các luồng hiện có của ứng dụng: cửa hàng, tài khoản khách hàng và quản trị đơn hàng.

## 1. Mục tiêu

- Làm rõ cấu trúc trang và luồng thao tác trước khi triển khai giao diện.
- Kiểm tra khả năng tìm sản phẩm và hoàn tất đặt hàng trên desktop lẫn mobile.
- Thống nhất nội dung, trạng thái giao diện và hành vi giữa thiết kế với ứng dụng.
- Bàn giao thiết kế đủ chi tiết để triển khai bằng Blade, CSS và JavaScript hiện có.

## 2. Công cụ

| Công cụ | Dùng để |
| --- | --- |
| **Figma Design** | Tạo wireframe, giao diện hoàn chỉnh, component, biến thiết kế và các kích thước màn hình. |
| **Figma Prototype** | Nối các frame, mô phỏng tương tác và trình bày luồng người dùng. |
| **FigJam** | Lập sơ đồ hành trình, sitemap, luồng nghiệp vụ và ghi chú trong buổi review. |
| **Trình duyệt và ứng dụng Shop Laravel** | Đối chiếu giao diện đang chạy, nội dung, trạng thái và hành vi thực tế. |
| **DevTools của trình duyệt** | Kiểm tra responsive, kích thước viewport và lỗi hiển thị sau khi triển khai. |

Figma là nguồn tham chiếu cho bố cục và thông số giao diện; ứng dụng đang chạy là nguồn tham chiếu cho nghiệp vụ và hành vi thực tế.

## 3. Phạm vi màn hình

Thiết kế theo mức ưu tiên sau:

1. **Mua hàng:** Trang chủ → danh sách sản phẩm → chi tiết sản phẩm/chọn màu và size → giỏ hàng → đăng nhập nếu cần → thanh toán/gửi đơn → xác nhận hoặc theo dõi đơn.
2. **Tài khoản khách hàng:** Đăng nhập/đăng ký → tài khoản → danh sách đơn → chi tiết đơn.
3. **Quản trị:** Danh sách sản phẩm và biểu mẫu sản phẩm; danh sách đơn hàng → cập nhật trạng thái.

Các màn hình này tương ứng với các view trong `resources/views` và các route trong `routes/web.php`. Trước khi thêm luồng mới vào prototype, xác nhận nghiệp vụ và route đó đã có hoặc được yêu cầu bổ sung.

## 4. Quy trình thiết kế

### Bước 1 — Chốt yêu cầu và người dùng

- Ghi mục tiêu, người dùng chính, thiết bị ưu tiên và tiêu chí hoàn thành.
- Tách nhu cầu của khách hàng và quản trị viên; không gộp quyền hoặc thao tác quản trị vào luồng khách.
- Liệt kê dữ liệu đầu vào, điều kiện thành công, lỗi và trường hợp rỗng cho từng trang.
- Ghi rõ nội dung chưa được hỗ trợ để không đưa vào prototype như một tính năng có thật.

**Đầu ra:** brief ngắn, danh sách màn hình và tiêu chí chấp nhận.

### Bước 2 — Lập sitemap và user flow

Trong FigJam hoặc trang riêng trong Figma, vẽ các trang và điều kiện chuyển tiếp. Bắt đầu với luồng mua hàng chính:

```text
Trang chủ
  → Sản phẩm
  → Chi tiết sản phẩm
      → Chọn màu và size
      → Thêm vào giỏ
  → Giỏ hàng
      → Chỉnh số lượng / xóa sản phẩm
      → Thanh toán
          → Chưa đăng nhập: đăng nhập
          → Đã đăng nhập: nhập thông tin giao hàng
          → Gửi đơn
  → Đơn hàng của tôi
```

Vẽ thêm nhánh tìm kiếm không có kết quả, sản phẩm hết hàng/không có biến thể, lỗi biểu mẫu và giỏ hàng trống. Chỉ mô phỏng bước thanh toán trực tuyến nếu ứng dụng có tích hợp phương thức thanh toán tương ứng.

**Đầu ra:** sơ đồ điều hướng, các điểm quyết định và danh sách trạng thái cần thiết kế.

### Bước 3 — Phác thảo wireframe

- Tạo page `01 - Wireframes`; dùng frame theo preset thiết bị trong Figma.
- Bắt đầu bằng khung xám, ưu tiên thứ bậc nội dung, vị trí hành động chính và khả năng quét thông tin.
- Dùng dữ liệu mẫu có định dạng giống dữ liệu thật: tên sản phẩm, giá VND, màu, size, tồn kho và trạng thái đơn.
- Thiết kế mobile trước cho các luồng mua hàng; sau đó mở rộng lên desktop, không chỉ thu nhỏ nguyên bố cục desktop.
- Gắn ghi chú cho hành vi động: lọc sản phẩm, chọn biến thể, cập nhật giỏ, validation và điều hướng sau đăng nhập.

**Đầu ra:** wireframe cho từng màn hình chính và trạng thái quan trọng, được liên kết theo user flow.

### Bước 4 — Tạo UI và component

- Tạo page `02 - UI`; thống nhất màu sắc, kiểu chữ, khoảng cách, lưới, bo góc và trạng thái tương tác.
- Dùng Figma Components và Variants cho thành phần lặp lại: header, nút, ô nhập, thẻ sản phẩm, thông báo, nhãn trạng thái và phân trang.
- Đặt tên layer/component theo vai trò, ví dụ `Button/Primary`, `Field/Text`, `ProductCard/Default`, `Notice/Error`.
- Tạo trạng thái tối thiểu cho component có hành vi: mặc định, hover/focus, disabled, lỗi và thành công khi phù hợp.
- Không thể hiện chỉ bằng màu sắc các trạng thái như hết hàng hoặc đơn bị hủy; luôn có nhãn hoặc văn bản đi kèm.

**Đầu ra:** giao diện hoàn chỉnh và thư viện component có thể tái sử dụng.

### Bước 5 — Tạo prototype tương tác

- Tạo page `03 - Prototype`; nhân bản hoặc dùng instance từ page UI, tránh sửa lệch bản thiết kế gốc.
- Nối các điểm chạm chính: điều hướng, tìm/lọc, chọn màu-size, thêm vào giỏ, thay đổi số lượng, đăng nhập và gửi đơn.
- Dùng overlay cho menu/dialog khi phù hợp; dùng frame riêng cho trạng thái trang hoặc phản hồi sau thao tác.
- Đặt điểm bắt đầu prototype theo từng vai trò: khách chưa đăng nhập, khách đã đăng nhập và quản trị viên.
- Dùng tương tác đơn giản, có thể dự đoán; không giả lập xử lý nền hoặc kết quả nghiệp vụ mà ứng dụng chưa có.

**Đầu ra:** prototype có thể bấm xuyên suốt các luồng ưu tiên, không có điểm kết thúc cụt hoặc liên kết sai.

### Bước 6 — Review và kiểm thử

- Cho người review thực hiện nhiệm vụ mà không hướng dẫn trước, ví dụ: tìm một sản phẩm, chọn size, cập nhật giỏ và gửi đơn.
- Kiểm tra đủ các nhánh thành công, lỗi, loading (nếu có), rỗng và disabled.
- Kiểm tra nội dung tiếng Việt, định dạng tiền, độ dài tên sản phẩm, thông báo validation và tính nhất quán của nút quay lại.
- Kiểm tra khả năng dùng bằng bàn phím, focus dễ nhận thấy, nhãn trường biểu mẫu và độ tương phản.
- Thử prototype ở viewport mobile và desktop; sửa vấn đề rồi review lại trước khi bàn giao.

**Đầu ra:** danh sách vấn đề đã xử lý và phiên bản prototype được duyệt.

### Bước 7 — Bàn giao cho phát triển

- Ghi trạng thái thiết kế: `Draft`, `In review`, `Approved` hoặc `Implemented`.
- Với mỗi frame, ghi tên route/view hiện có nếu đã xác định; ghi rõ phần nào cần thay đổi nghiệp vụ.
- Bổ sung thông số layout, kích thước, khoảng cách, màu, kiểu chữ, asset và responsive behavior.
- Cung cấp link Figma có quyền truy cập phù hợp; giữ component và asset ở trạng thái inspectable.
- Đính kèm tiêu chí chấp nhận và liên kết prototype; cập nhật ghi chú khi thiết kế thay đổi sau khi duyệt.

**Đầu ra:** link Figma, luồng prototype, thông số thiết kế, asset và tiêu chí nghiệm thu.

## 5. Danh sách trạng thái cần có

| Khu vực | Trạng thái cần mô tả |
| --- | --- |
| Danh sách sản phẩm | Mặc định, đang lọc, không có kết quả, lỗi tải nếu được ứng dụng hỗ trợ, phân trang. |
| Chi tiết sản phẩm | Chưa chọn biến thể, đã chọn màu, đã chọn màu và size, hết hàng, thêm vào giỏ thành công, lỗi nhập liệu. |
| Giỏ hàng | Có sản phẩm, trống, cập nhật số lượng thành công, xóa sản phẩm, lỗi tồn kho. |
| Đăng nhập/đăng ký | Mặc định, lỗi xác thực/validation, gửi thành công hoặc trạng thái sau điều hướng. |
| Thanh toán | Biểu mẫu mặc định, trường lỗi, giỏ không hợp lệ, gửi đơn và trạng thái sau khi tạo đơn. |
| Đơn hàng | Danh sách có dữ liệu, danh sách rỗng, chi tiết đơn và các trạng thái đơn mà hệ thống hỗ trợ. |
| Quản trị đơn hàng | Bảng có dữ liệu, không có đơn, cập nhật trạng thái và thông báo kết quả. |

Chỉ thiết kế trạng thái loading hoặc lỗi máy chủ nếu sản phẩm có yêu cầu hiển thị trạng thái đó; không tự suy diễn một hành vi mới từ mockup.

## 6. Cấu trúc file Figma đề xuất

```text
Shop Laravel — [Tên tính năng]
├── 00 - Cover & Brief
├── 01 - User Flows
├── 02 - Wireframes
├── 03 - UI Kit
├── 04 - Screens
├── 05 - Prototype
└── 06 - Handoff & Review
```

Quy ước:

- Đặt tên frame theo `Vai trò / Trang / Thiết bị / Trạng thái`, ví dụ `Khách / Giỏ hàng / Mobile / Trống`.
- Dùng Auto Layout và component cho vùng lặp lại; đặt variant theo hành vi thay vì theo vị trí trên canvas.
- Giữ một frame rõ ràng làm điểm bắt đầu cho mỗi prototype.
- Không ghi dữ liệu cá nhân hoặc thông tin nhạy cảm vào nội dung mẫu.

## 7. Tiêu chí hoàn thành

- Mọi màn hình trong phạm vi đều có wireframe và frame UI tương ứng.
- Prototype chạy được các luồng ưu tiên và các nhánh lỗi/rỗng quan trọng.
- Thiết kế mobile và desktop đã được kiểm tra; trạng thái focus và disabled không bị bỏ sót.
- Tên frame/component rõ ràng, link prototype còn hoạt động và file có quyền truy cập phù hợp.
- Các khác biệt với chức năng hiện tại được ghi rõ, không trình bày như thể đã được triển khai.
