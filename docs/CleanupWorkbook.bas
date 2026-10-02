Attribute VB_Name = "CleanupWorkbook"
'================================================================
'  DON DEP WORKBOOK - GIAM DUNG LUONG, MO NHANH HON
'  - GIU NGUYEN 100% du lieu hoa don va tat ca sheet
'  - Chi cat bo phan "mo thua": Conditional Formatting / Data
'    Validation ap cho ca cot, va vung trong duoi/phai du lieu
'  BAT BUOC: SAO LUU (copy) FILE TRUOC KHI CHAY
'================================================================

Sub CleanupWorkbook()
    Dim ws As Worksheet
    Dim lastCell As Range, lastCellCol As Range
    Dim lastRow As Long, lastCol As Long
    Dim dataBox As Range
    Dim i As Long
    Dim fc As Object
    Dim appl As Range, newRng As Range
    Dim sheetCount As Long

    If MsgBox("Ban da SAO LUU file chua? Macro se cat bo dinh dang thua tren tat ca sheet." & vbCrLf & _
              "Du lieu hoa don KHONG bi xoa. Tiep tuc?", vbYesNo + vbQuestion, "Xac nhan") <> vbYes Then
        Exit Sub
    End If

    Application.ScreenUpdating = False
    Application.Calculation = xlCalculationManual
    Application.DisplayAlerts = False
    Application.EnableEvents = False

    For Each ws In ThisWorkbook.Worksheets
        ' ---- Tim o cuoi cung that su chua du lieu ----
        lastRow = 1: lastCol = 1
        Set lastCell = Nothing: Set lastCellCol = Nothing
        On Error Resume Next
        Set lastCell = ws.Cells.Find(What:="*", LookIn:=xlFormulas, _
            SearchOrder:=xlByRows, SearchDirection:=xlPrevious)
        Set lastCellCol = ws.Cells.Find(What:="*", LookIn:=xlFormulas, _
            SearchOrder:=xlByColumns, SearchDirection:=xlPrevious)
        On Error GoTo 0

        If Not lastCell Is Nothing Then lastRow = lastCell.Row
        If Not lastCellCol Is Nothing Then lastCol = lastCellCol.Column
        If lastRow < 1 Then lastRow = 1
        If lastCol < 1 Then lastCol = 1

        Set dataBox = ws.Range(ws.Cells(1, 1), ws.Cells(lastRow, lastCol))

        ' ---- 1) Thu gon Conditional Formatting ap ca cot ----
        For i = ws.Cells.FormatConditions.Count To 1 Step -1
            On Error Resume Next
            Set fc = ws.Cells.FormatConditions(i)
            Set appl = fc.AppliesTo
            Set newRng = Application.Intersect(appl, dataBox)
            If newRng Is Nothing Then
                fc.Delete
            ElseIf newRng.Address <> appl.Address Then
                fc.ModifyAppliesToRange newRng
            End If
            On Error GoTo 0
        Next i

        ' ---- 2) Xoa Data Validation o vung trong duoi du lieu ----
        On Error Resume Next
        If lastRow < ws.Rows.Count Then
            ws.Range(ws.Rows(lastRow + 1), ws.Rows(ws.Rows.Count)).Validation.Delete
        End If
        On Error GoTo 0

        ' ---- 3) Xoa han dong/cot RONG duoi va phai du lieu ----
        On Error Resume Next
        If lastRow < ws.Rows.Count Then
            ws.Range(ws.Rows(lastRow + 1), ws.Rows(ws.Rows.Count)).Delete
        End If
        If lastCol < ws.Columns.Count Then
            ws.Range(ws.Columns(lastCol + 1), ws.Columns(ws.Columns.Count)).Delete
        End If
        On Error GoTo 0

        ' ---- 4) Buoc Excel tinh lai UsedRange ----
        Dim dummy As Long
        dummy = ws.UsedRange.Rows.Count

        sheetCount = sheetCount + 1
    Next ws

    Application.EnableEvents = True
    Application.DisplayAlerts = True
    Application.Calculation = xlCalculationAutomatic
    Application.ScreenUpdating = True

    MsgBox "Da don xong " & sheetCount & " sheet." & vbCrLf & _
           "Buoc cuoi: Luu file sang dinh dang .xlsb (Excel Binary) de nhe va mo nhanh hon.", _
           vbInformation, "Hoan tat"
End Sub
