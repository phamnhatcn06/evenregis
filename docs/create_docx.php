<?php
// Simple DOCX generator without external libraries

$content = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>

<w:p><w:pPr><w:jc w:val="center"/><w:pStyle w:val="Heading1"/></w:pPr>
<w:r><w:rPr><w:b/><w:sz w:val="48"/></w:rPr><w:t>LỊCH THI ĐẤU BÓNG BÀN</w:t></w:r></w:p>

<w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Thông tin chung:</w:t></w:r></w:p>
<w:p><w:r><w:t>• Thời gian: 2 ngày</w:t></w:r></w:p>
<w:p><w:r><w:t>• Buổi sáng: 7:30 - 11:30</w:t></w:r></w:p>
<w:p><w:r><w:t>• Buổi chiều: 13:00 - 17:30</w:t></w:r></w:p>
<w:p><w:r><w:t>• Thời lượng mỗi trận: 30 phút</w:t></w:r></w:p>

<w:p><w:r><w:t></w:t></w:r></w:p>
<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:t>NGÀY 1 - BUỔI SÁNG (7:30 - 11:30)</w:t></w:r></w:p>

<w:tbl>
<w:tblPr><w:tblW w:w="9000" w:type="dxa"/><w:tblBorders>
<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/><w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/><w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>
</w:tblBorders></w:tblPr>
<w:tr><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>STT</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Giờ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Nội dung</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Vòng đấu</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Trận</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>1</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>07:30-08:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Tứ kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>ĐN1 vs ĐN8</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>2</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>08:00-08:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nữ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Tứ kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>DNữ1 vs DNữ8</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>3</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>08:30-09:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Tứ kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>ĐN2 vs ĐN7</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>4</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>09:00-09:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nữ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Tứ kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>DNữ2 vs DNữ7</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>5</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>09:30-10:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng A</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>A1 vs A2</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>6</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>10:00-10:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng B</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>B1 vs B2</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>7</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>10:30-11:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Tứ kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>ĐN3 vs ĐN6</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>8</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>11:00-11:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nữ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Tứ kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>DNữ3 vs DNữ6</w:t></w:r></w:p></w:tc></w:tr>
</w:tbl>

<w:p><w:r><w:t></w:t></w:r></w:p>
<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:t>NGÀY 1 - BUỔI CHIỀU (13:00 - 17:30)</w:t></w:r></w:p>

<w:tbl>
<w:tblPr><w:tblW w:w="9000" w:type="dxa"/><w:tblBorders>
<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/><w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/><w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>
</w:tblBorders></w:tblPr>
<w:tr><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>STT</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Giờ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Nội dung</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Vòng đấu</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Trận</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>9</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>13:00-13:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Tứ kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>ĐN4 vs ĐN5</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>10</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>13:30-14:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nữ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Tứ kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>DNữ4 vs DNữ5</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>11</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>14:00-14:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng A</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>A3 vs A4</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>12</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>14:30-15:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng B</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>B1 vs B3</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>13</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>15:00-15:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng A</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>A1 vs A3</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>14</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>15:30-16:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng B</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>B2 vs B3</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>15</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>16:00-16:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng A</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>A2 vs A4</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>16</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>16:30-17:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng A</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>A1 vs A4</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>17</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>17:00-17:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng A</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>A2 vs A3</w:t></w:r></w:p></w:tc></w:tr>
</w:tbl>

<w:p><w:r><w:t></w:t></w:r></w:p>
<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:t>NGÀY 2 - BUỔI SÁNG (7:30 - 11:30)</w:t></w:r></w:p>

<w:tbl>
<w:tblPr><w:tblW w:w="9000" w:type="dxa"/><w:tblBorders>
<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/><w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/><w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>
</w:tblBorders></w:tblPr>
<w:tr><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>STT</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Giờ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Nội dung</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Vòng đấu</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Trận</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>18</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>07:30-08:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bảng A</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>A3 vs A4</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>19</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>08:00-08:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bán kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thắng TK1 vs Thắng TK2</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>20</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>08:30-09:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nữ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bán kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thắng TK1 vs Thắng TK2</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>21</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>09:00-09:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bán kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thắng TK3 vs Thắng TK4</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>22</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>09:30-10:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nữ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bán kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thắng TK3 vs Thắng TK4</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>23</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>10:00-10:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bán kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Nhất A vs Nhì B</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>24</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>10:30-11:00</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Bán kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Nhất B vs Nhì A</w:t></w:r></w:p></w:tc></w:tr>
</w:tbl>

<w:p><w:r><w:t></w:t></w:r></w:p>
<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:t>NGÀY 2 - BUỔI CHIỀU - CHUNG KẾT (13:00 - 17:30)</w:t></w:r></w:p>

<w:tbl>
<w:tblPr><w:tblW w:w="9000" w:type="dxa"/><w:tblBorders>
<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/><w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/><w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>
</w:tblBorders></w:tblPr>
<w:tr><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>STT</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Giờ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Nội dung</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Vòng đấu</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Trận</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>25</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>14:00-14:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đôi Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Chung kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thắng BK1 vs Thắng BK2</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>26</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>15:00-15:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nữ</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Chung kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thắng BK1 vs Thắng BK2</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>27</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>16:00-16:30</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Đơn Nam</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Chung kết</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Thắng BK1 vs Thắng BK2</w:t></w:r></w:p></w:tc></w:tr>
</w:tbl>

<w:p><w:r><w:t></w:t></w:r></w:p>
<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:t>CHIA BẢNG ĐÔI NAM (7 đội)</w:t></w:r></w:p>
<w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Bảng A (4 đội):</w:t></w:r><w:r><w:t> Đội A1, Đội A2, Đội A3, Đội A4</w:t></w:r></w:p>
<w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Bảng B (3 đội):</w:t></w:r><w:r><w:t> Đội B1, Đội B2, Đội B3</w:t></w:r></w:p>

<w:p><w:r><w:t></w:t></w:r></w:p>
<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="32"/></w:rPr><w:t>GHI CHÚ</w:t></w:r></w:p>
<w:p><w:r><w:t>1. Tránh trùng lặp VĐV: Các trận Đơn Nam và Đôi Nam được xếp xen kẽ với Đơn Nữ để VĐV tham gia cả 2 nội dung có thời gian nghỉ.</w:t></w:r></w:p>
<w:p><w:r><w:t>2. Vòng tròn Bảng A: 6 trận (mỗi đội gặp nhau 1 lần)</w:t></w:r></w:p>
<w:p><w:r><w:t>3. Vòng tròn Bảng B: 3 trận (mỗi đội gặp nhau 1 lần)</w:t></w:r></w:p>
<w:p><w:r><w:t>4. Bán kết Đôi Nam: Nhất Bảng A vs Nhì Bảng B; Nhất Bảng B vs Nhì Bảng A</w:t></w:r></w:p>

</w:body>
</w:document>
XML;

$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>';

$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>';

$zip = new ZipArchive();
$filename = __DIR__ . '/lich_thi_dau.docx';

if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $zip->addFromString('[Content_Types].xml', $contentTypes);
    $zip->addFromString('_rels/.rels', $rels);
    $zip->addFromString('word/document.xml', $content);
    $zip->close();
    echo "Created: $filename\n";
} else {
    echo "Failed to create DOCX\n";
}
