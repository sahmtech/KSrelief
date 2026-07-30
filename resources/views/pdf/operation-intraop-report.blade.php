<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>OPERATIVE NOTE</title>
    <style>
        @page {
            margin: {{ (float) ($formatting['page_margin_mm'] ?? 10) }}mm;
        }

        * { box-sizing: border-box; }

        body {
            font-family: "Times-Roman", "Times New Roman", Times, serif;
            font-size: {{ (float) ($formatting['body_font_size_pt'] ?? 11) }}pt;
            color: #000;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .page {
            border: {{ (float) ($formatting['border_width_pt'] ?? 2.5) }}pt solid #000;
            padding: 8mm 8mm {{ (float) ($formatting['page_padding_bottom_mm'] ?? 22) }}mm;
        }

        .page-break {
            page-break-after: always;
        }

        .logo-wrap {
            text-align: center;
            margin: 0 0 4px;
        }

        .logo-wrap img {
            max-height: 48px;
            max-width: 280px;
        }

        .doc-title {
            text-align: center;
            font-size: {{ (float) ($formatting['title_font_size_pt'] ?? 16) }}pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 4px 0 12px;
            text-transform: uppercase;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .meta td {
            vertical-align: top;
            padding: 2px 0 3px;
            font-size: {{ (float) ($formatting['body_font_size_pt'] ?? 11) }}pt;
        }

        .meta .left { width: 55%; padding-right: 8px; }
        .meta .right { width: 45%; }

        .full-line {
            margin: 2px 0 3px;
            font-size: {{ (float) ($formatting['body_font_size_pt'] ?? 11) }}pt;
        }

        .label { font-weight: normal; }
        .value { font-weight: bold; }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0 7px;
            font-size: 11.5pt;
        }

        .note p {
            margin: 0 0 8px;
            text-align: justify;
            font-size: {{ max(9, ((float) ($formatting['body_font_size_pt'] ?? 11)) - 0.2) }}pt;
            line-height: 1.36;
        }

        .fill { font-weight: bold; }

        .orders-title {
            font-weight: bold;
            margin: 6px 0 10px;
            font-size: 12pt;
        }

        .orders {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .orders li {
            margin: 0 0 7px;
            font-size: {{ (float) ($formatting['body_font_size_pt'] ?? 11) }}pt;
            line-height: 1.4;
        }

        .signoff {
            margin-top: 24px;
            font-size: {{ (float) ($formatting['body_font_size_pt'] ?? 11) }}pt;
            line-height: 1.8;
        }

        .signoff .row {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="page page-break">
        <div class="logo-wrap">
            @if(!empty($logo_src))
                <img src="{{ $logo_src }}" alt="KSRelief">
            @else
                <strong>KING SALMAN HUMANITARIAN AID &amp; RELIEF CENTRE</strong>
            @endif
        </div>

        <div class="doc-title">OPERATIVE NOTE</div>

        <table class="meta">
            <tr>
                <td class="left">
                    <span class="label">Date of surgery:</span>
                    <span class="value">{{ $operation_date !== '' ? $operation_date : '....................' }}</span>
                </td>
                <td class="right">
                    <span class="label">Surgeon:</span>
                    <span class="value">{{ $surgeon !== '' ? $surgeon : '....................' }}</span>
                </td>
            </tr>
            <tr>
                <td class="left">
                    <span class="label">Preop diagnosis:</span>
                    <span class="value">{{ $preop_diagnosis !== '' ? $preop_diagnosis : '....................' }}</span>
                </td>
                <td class="right">
                    <span class="label">Post op diagnosis:</span>
                    <span class="value">{{ $postop_diagnosis !== '' ? $postop_diagnosis : '....................' }}</span>
                </td>
            </tr>
            <tr>
                <td class="left">
                    <span class="label">Surgery:</span>
                    <span class="value">{{ $surgery_line !== '' ? $surgery_line : '....................' }}</span>
                </td>
                <td class="right">
                    <span class="label">Implant type:</span>
                    <span class="value">{{ $implant_type !== '' ? $implant_type : '....................' }}</span>
                </td>
            </tr>
        </table>

        <div class="full-line">
            <span class="label">Insertion through :</span>
            <span class="value">{{ $insertion_through !== '' ? $insertion_through : '....................' }}</span>
        </div>

        <div class="full-line">
            <span class="label">Facial nerve monitor :</span>
            <span class="value">{{ $facial_nerve_monitor !== '' ? $facial_nerve_monitor : 'used' }}</span>
        </div>

        <div class="section-title">OPERATIVE NOTE:</div>

        <div class="note">
            @foreach($narrative_paragraphs_html as $paragraphHtml)
                <p>{!! $paragraphHtml !!}</p>
            @endforeach
        </div>
    </div>

    <div class="page">
        <div class="logo-wrap" style="text-align:left;">
            @if(!empty($logo_src))
                <img src="{{ $logo_src }}" alt="KSRelief" style="max-height:44px;">
            @endif
        </div>

        <div class="orders-title">Post- OP orders:</div>

        <ul class="orders">
            @foreach($post_op_orders as $order)
                <li>- {{ $order }}</li>
            @endforeach
        </ul>

        <div class="signoff">
            <div class="row">Done by: <span class="value">{{ $surgeon !== '' ? $surgeon : '....................' }}</span></div>
            <div class="row">Date: <span class="value">{{ $operation_date !== '' ? $operation_date : '....................' }}</span></div>
            <div class="row">Time: <span class="value">{{ $time_out !== '' ? $time_out : '....................' }}</span></div>
        </div>
    </div>
</body>
</html>
