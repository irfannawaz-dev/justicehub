<!DOCTYPE html>
@php
    $lang = request('lang', 'en');
    $isRtl = in_array($lang, ['ur', 'sd']);

    $t = [
        'en' => [
            'pageTitle'       => 'Intake Token',
            'printHint'       => 'To print 4 slips per A4, choose <b>4 pages per sheet</b> in your print dialog.',
            'back'            => '← Back',
            'print'           => '🖨 Print',
            'clientIntake'    => 'Client Intake Token',
            'justiceHub'      => 'Justice Hub',
            'tagline'         => 'A One-Stop Solution Closer to Communities',
            'date'            => 'Date',
            'staff'           => 'Staff',
            'clientInfo'      => 'Client Information',
            'fullName'        => 'Full Name',
            'fatherHusband'   => 'Father / Husband',
            'gender'          => 'Gender',
            'age'             => 'Age',
            'cnic'            => 'CNIC',
            'mobile'          => 'Mobile',
            'address'         => 'Address',
            'pathwaySection'  => 'Pathway & Assignment',
            'referredFrom'    => 'Referred From',
            'pathway'         => 'Pathway',
            'specific'        => 'Specific',
            'assignedStaff'   => 'Assigned Lawyer / Staff',
            'staffContact'    => 'Staff Contact',
            'clientSig'       => 'Client Signature / Thumb',
            'staffSig'        => 'Staff Signature',
            'officeStamp'     => 'Office Stamp',
            'slaccLabel'      => 'SLACC · 24/7 Toll-Free',
            'slaccSub'        => 'Free legal advice, any time.',
            'feedbackLabel'   => 'LAS · Feedback',
            'feedbackSub'     => 'Questions or complaints.',
        ],
        'ur' => [
            'pageTitle'       => 'انٹیک ٹوکن',
            'printHint'       => 'A4 پر 4 سلپ پرنٹ کرنے کے لیے، پرنٹ ڈائیلاگ میں <b>4 صفحات فی شیٹ</b> منتخب کریں۔',
            'back'            => 'واپس ←',
            'print'           => '🖨 پرنٹ',
            'clientIntake'    => 'کلائنٹ انٹیک ٹوکن',
            'justiceHub'      => 'جسٹس ہب',
            'tagline'         => 'کمیونٹیز کے قریب ایک مکمل حل',
            'date'            => 'تاریخ',
            'staff'           => 'اسٹاف',
            'clientInfo'      => 'کلائنٹ کی معلومات',
            'fullName'        => 'پورا نام',
            'fatherHusband'   => 'والد / شوہر',
            'gender'          => 'جنس',
            'age'             => 'عمر',
            'cnic'            => 'شناختی کارڈ',
            'mobile'          => 'موبائل',
            'address'         => 'پتہ',
            'pathwaySection'  => 'پاتھ وے اور تفویض',
            'referredFrom'    => 'حوالہ',
            'pathway'         => 'پاتھ وے',
            'specific'        => 'مخصوص',
            'assignedStaff'   => 'مقرر وکیل / اسٹاف',
            'staffContact'    => 'اسٹاف رابطہ',
            'clientSig'       => 'کلائنٹ دستخط / انگوٹھا',
            'staffSig'        => 'اسٹاف دستخط',
            'officeStamp'     => 'دفتری مہر',
            'slaccLabel'      => 'SLACC · 24/7 ٹول فری',
            'slaccSub'        => 'مفت قانونی مشورہ، کسی بھی وقت۔',
            'feedbackLabel'   => 'LAS · آراء',
            'feedbackSub'     => 'سوالات یا شکایات۔',
        ],
        'sd' => [
            'pageTitle'       => 'انٽيڪ ٽوڪن',
            'printHint'       => 'A4 تي 4 سلپ ڇپائڻ لاءِ، پرنٽ ڊائلاگ ۾ <b>4 صفحا في شيٽ</b> چونڊيو.',
            'back'            => 'واپس ←',
            'print'           => '🖨 ڇاپيو',
            'clientIntake'    => 'ڪلائنٽ انٽيڪ ٽوڪن',
            'justiceHub'      => 'جسٽس هب',
            'tagline'         => 'ڪميونٽيز جي ويجهو هڪ مڪمل حل',
            'date'            => 'تاريخ',
            'staff'           => 'اسٽاف',
            'clientInfo'      => 'ڪلائنٽ جي ڄاڻ',
            'fullName'        => 'پورو نالو',
            'fatherHusband'   => 'پيءُ / مڙس',
            'gender'          => 'جنس',
            'age'             => 'عمر',
            'cnic'            => 'سڃاڻپ ڪارڊ',
            'mobile'          => 'موبائيل',
            'address'         => 'پتو',
            'pathwaySection'  => 'پاٿ وي ۽ تفويض',
            'referredFrom'    => 'حوالو',
            'pathway'         => 'پاٿ وي',
            'specific'        => 'مخصوص',
            'assignedStaff'   => 'مقرر وڪيل / اسٽاف',
            'staffContact'    => 'اسٽاف رابطو',
            'clientSig'       => 'ڪلائنٽ دستخط / آڱوٺو',
            'staffSig'        => 'اسٽاف دستخط',
            'officeStamp'     => 'آفيس مهر',
            'slaccLabel'      => 'SLACC · 24/7 ٽول فري',
            'slaccSub'        => 'مفت قانوني صلاح، ڪنهن به وقت.',
            'feedbackLabel'   => 'LAS · راءِ',
            'feedbackSub'     => 'سوال يا شڪايتون.',
        ],
    ];

    $l = $t[$lang] ?? $t['en'];

    // ── Data value translations (English → Urdu / Sindhi) ──
    $dataTranslations = [
        'ur' => [
            // Gender
            'Male' => 'مرد', 'Female' => 'عورت', 'Other' => 'دیگر', 'Transgender' => 'خواجہ سرا',
            // Marital Status
            'Single' => 'غیر شادی شدہ', 'Married' => 'شادی شدہ', 'Divorced' => 'طلاق یافتہ', 'Widowed' => 'بیوہ/بیوا', 'Separated' => 'علیحدہ',
            // Case Status
            'Active' => 'فعال', 'Closed' => 'بند', 'Settlement' => 'تصفیہ', 'Pending Approval' => 'منظوری زیر التوا', 'Rejected' => 'مسترد',
            // Urgency
            'Immediate' => 'فوری', 'High' => 'اعلیٰ', 'Medium' => 'درمیانی', 'Low' => 'کم',
            // Risk
            'High risk' => 'زیادہ خطرہ', 'Medium risk' => 'درمیانی خطرہ', 'Low risk' => 'کم خطرہ',
            // Pathways
            'Legal Advice / Consultation' => 'قانونی مشورہ / مشاورت',
            'Court Representation' => 'عدالتی نمائندگی',
            'Representation in Court' => 'عدالت میں نمائندگی',
            'Mediation' => 'ثالثی',
            'ADR / Dispute Resolution Support' => 'متبادل تنازعات حل',
            'Government Department / Public Institution' => 'سرکاری محکمہ / عوامی ادارہ',
            'Civil Society / NGO / CSO / NPO' => 'سول سوسائٹی / این جی او',
            'Other' => 'دیگر',
            // Specific pathways
            'Justice Hub Lawyer' => 'جسٹس ہب وکیل',
            'Justice Hub Accredited Mediator' => 'جسٹس ہب تصدیق شدہ ثالث',
            'SLACC' => 'ایس ایل اے سی سی',
            'Provincial Ombudsman / Mohtasib' => 'صوبائی محتسب',
            'NADRA' => 'نادرا',
            'Police' => 'پولیس',
            'Revenue Department' => 'محکمہ مال',
            'Health Department' => 'محکمہ صحت',
            'Education Department' => 'محکمہ تعلیم',
            'Social Welfare' => 'سماجی بہبود',
            // Referral sources
            'Walk-in' => 'براہ راست آمد',
            'Website / Social Media' => 'ویب سائٹ / سوشل میڈیا',
            'Paralegal' => 'پیرالیگل',
            'Government Department' => 'سرکاری محکمہ',
            'NGO / CSO / NPO' => 'این جی او / سی ایس او',
            'Community Leader' => 'سماجی رہنما',
            'Bar Association' => 'بار ایسوسی ایشن',
            'Court Referral' => 'عدالتی حوالہ',
            'Police Referral' => 'پولیس حوالہ',
            'Self-referral' => 'خود حوالہ',
            'Other - please specify' => 'دیگر',
            // Primary issues
            'Family Law' => 'خاندانی قانون', 'Land Dispute' => 'زمینی تنازعہ',
            'Criminal Law' => 'فوجداری قانون', 'Civil Litigation' => 'دیوانی مقدمہ',
            'Labour Law' => 'محنت کا قانون', 'Consumer Rights' => 'صارفین کے حقوق',
            'Public Grievances' => 'عوامی شکایات', 'Documentation' => 'دستاویزات',
            'Domestic Violence' => 'گھریلو تشدد', 'Child Rights' => 'بچوں کے حقوق',
            'Women Rights' => 'خواتین کے حقوق', 'Property Dispute' => 'جائیداد کا تنازعہ',
            'Inheritance' => 'وراثت', 'Divorce / Khula' => 'طلاق / خلع',
            'Maintenance' => 'نان و نفقہ', 'Custody' => 'حضانت',
            'CNIC / Documentation' => 'شناختی کارڈ / دستاویزات',
            'Bail Matter' => 'ضمانت کا معاملہ',
            // Months
            'Jan' => 'جنوری', 'Feb' => 'فروری', 'Mar' => 'مارچ', 'Apr' => 'اپریل',
            'May' => 'مئی', 'Jun' => 'جون', 'Jul' => 'جولائی', 'Aug' => 'اگست',
            'Sep' => 'ستمبر', 'Oct' => 'اکتوبر', 'Nov' => 'نومبر', 'Dec' => 'دسمبر',
        ],
        'sd' => [
            // Gender
            'Male' => 'مرد', 'Female' => 'عورت', 'Other' => 'ٻيو', 'Transgender' => 'خواجه سرا',
            // Marital Status
            'Single' => 'اڻ وياهيل', 'Married' => 'وياهيل', 'Divorced' => 'طلاق يافته', 'Widowed' => 'بيوه', 'Separated' => 'الڳ',
            // Case Status
            'Active' => 'فعال', 'Closed' => 'بند', 'Settlement' => 'تصفيو', 'Pending Approval' => 'منظوري جي انتظار ۾', 'Rejected' => 'رد ٿيل',
            // Urgency
            'Immediate' => 'فوري', 'High' => 'مٿي', 'Medium' => 'وچولي', 'Low' => 'گهٽ',
            // Risk
            'High risk' => 'وڌيڪ خطرو', 'Medium risk' => 'وچولو خطرو', 'Low risk' => 'گهٽ خطرو',
            // Pathways
            'Legal Advice / Consultation' => 'قانوني صلاح / مشاورت',
            'Court Representation' => 'عدالتي نمائندگي',
            'Representation in Court' => 'عدالت ۾ نمائندگي',
            'Mediation' => 'ثالثي',
            'ADR / Dispute Resolution Support' => 'متبادل تڪرار حل',
            'Government Department / Public Institution' => 'سرڪاري محڪمو / عوامي ادارو',
            'Civil Society / NGO / CSO / NPO' => 'سول سوسائٽي / اين جي او',
            'Other' => 'ٻيو',
            // Specific pathways
            'Justice Hub Lawyer' => 'جسٽس هب وڪيل',
            'Justice Hub Accredited Mediator' => 'جسٽس هب تصديق ٿيل ثالث',
            'SLACC' => 'ايس ايل اي سي سي',
            'Provincial Ombudsman / Mohtasib' => 'صوبائي محتسب',
            'NADRA' => 'نادرا',
            'Police' => 'پوليس',
            'Revenue Department' => 'محڪمو مال',
            'Health Department' => 'محڪمو صحت',
            'Education Department' => 'محڪمو تعليم',
            'Social Welfare' => 'سماجي ڀلائي',
            // Referral sources
            'Walk-in' => 'سڌي آمد',
            'Website / Social Media' => 'ويب سائيٽ / سوشل ميڊيا',
            'Paralegal' => 'پيرا ليگل',
            'Government Department' => 'سرڪاري محڪمو',
            'NGO / CSO / NPO' => 'اين جي او / سي ايس او',
            'Community Leader' => 'سماجي اڳواڻ',
            'Bar Association' => 'بار ايسوسي ايشن',
            'Court Referral' => 'عدالتي حوالو',
            'Police Referral' => 'پوليس حوالو',
            'Self-referral' => 'پاڻ حوالو',
            'Other - please specify' => 'ٻيو',
            // Primary issues
            'Family Law' => 'خاندان قانون', 'Land Dispute' => 'زمين جو تڪرار',
            'Criminal Law' => 'فوجداري قانون', 'Civil Litigation' => 'ديواني مقدمو',
            'Labour Law' => 'مزدورن جو قانون', 'Consumer Rights' => 'صارفين جا حق',
            'Public Grievances' => 'عوامي شڪايتون', 'Documentation' => 'دستاويزات',
            'Domestic Violence' => 'گهريلو تشدد', 'Child Rights' => 'ٻارن جا حق',
            'Women Rights' => 'عورتن جا حق', 'Property Dispute' => 'جائداد جو تڪرار',
            'Inheritance' => 'وراثت', 'Divorce / Khula' => 'طلاق / خلع',
            'Maintenance' => 'نان و نفقو', 'Custody' => 'حضانت',
            'CNIC / Documentation' => 'سڃاڻپ ڪارڊ / دستاويزات',
            'Bail Matter' => 'ضمانت جو معاملو',
            // Months
            'Jan' => 'جنوري', 'Feb' => 'فيبروري', 'Mar' => 'مارچ', 'Apr' => 'اپريل',
            'May' => 'مئي', 'Jun' => 'جون', 'Jul' => 'جولائي', 'Aug' => 'آگسٽ',
            'Sep' => 'سيپٽمبر', 'Oct' => 'آڪٽوبر', 'Nov' => 'نومبر', 'Dec' => 'ڊسمبر',
        ],
    ];

    // Helper to translate a data value
    $tr = function($val) use ($lang, $dataTranslations) {
        if ($lang === 'en' || !$val) return $val;
        return $dataTranslations[$lang][$val] ?? $val;
    };

    // Translate date (replace English month abbreviation)
    $intakeDateRaw = $case->intake_date ? \Carbon\Carbon::parse($case->intake_date) : null;
    if ($intakeDateRaw && $lang !== 'en') {
        $monthAbbr = $intakeDateRaw->format('M');
        $translatedMonth = $dataTranslations[$lang][$monthAbbr] ?? $monthAbbr;
        $intakeDate = $intakeDateRaw->format('d') . ' ' . $translatedMonth . ' ' . $intakeDateRaw->format('Y');
    } else {
        $intakeDate = $intakeDateRaw ? $intakeDateRaw->format('d M Y') : '—';
    }

    $address = $case->full_address ?? trim(implode(', ', array_filter([$case->union_council, $case->tehsil, $case->district])), ', ') ?: '—';
@endphp
<html lang="{{ $lang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $l['pageTitle'] }} — {{ $case->case_uid }}</title>
    @if($isRtl)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: {{ $isRtl ? "'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Mehr Nastaliq', " : '' }}'Segoe UI', Arial, sans-serif;
            background: #ccc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 28px 16px;
            min-height: 100vh;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
        }

        .no-print {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            width: 105mm;
            direction: ltr;
        }
        .no-print .hint {
            flex: 1;
            font-size: 10px;
            color: #555;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .lang-btns {
            display: flex;
            gap: 4px;
            margin-bottom: 10px;
            width: 105mm;
            direction: ltr;
        }
        .lang-btns a {
            padding: 5px 14px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #bbb;
            font-family: 'Segoe UI', Arial, sans-serif;
            cursor: pointer;
        }
        .lang-btns a.active {
            background: #111;
            color: #fff;
            border-color: #111;
        }
        .lang-btns a:not(.active) {
            background: #fff;
            color: #333;
        }

        /* ── Slip card: A6 size ── */
        .slip {
            width: 105mm;
            background: #fff;
            border: 1.5px solid #222;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .sl-header {
            display: flex;
            align-items: center;
            padding: 5px 7px;
            border-bottom: 1.5px solid #111;
            gap: 6px;
            direction: ltr;
        }
        .sl-las .las-word {
            font-size: 15px;
            font-weight: 900;
            color: #111;
            letter-spacing: 3px;
            line-height: 1;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .sl-las .las-rule { height: 1.5px; background: #c9a227; margin: 2px 0; }
        .sl-las .las-sub {
            font-size: 5px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; color: #111; white-space: nowrap;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .sl-center {
            flex: 1; text-align: center;
            border-left: 0.5px solid #ccc; border-right: 0.5px solid #ccc;
            padding: 0 6px;
        }
        .sl-center .eyebrow {
            font-size: {{ $isRtl ? '7px' : '5px' }}; letter-spacing: {{ $isRtl ? '0' : '2px' }}; text-transform: uppercase;
            color: #c9a227; font-weight: 700;
        }
        .sl-center h1 {
            font-size: {{ $isRtl ? '14px' : '12px' }}; font-weight: 900; color: #111;
            letter-spacing: {{ $isRtl ? '0' : '3px' }}; text-transform: {{ $isRtl ? 'none' : 'uppercase' }}; line-height: {{ $isRtl ? '1.6' : '1.1' }};
        }
        .sl-center .tagline { font-size: {{ $isRtl ? '6px' : '5px' }}; color: #999; font-style: italic; }
        .sl-seal {
            width: 34px; height: 34px; border: 1.5px solid #111; border-radius: 50%;
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; position: relative; flex-shrink: 0;
        }
        .sl-seal::before {
            content: ''; position: absolute; inset: 3px;
            border-radius: 50%; border: 0.5px solid #c9a227;
        }
        .sl-seal .seal-jh { font-size: 10px; font-weight: 900; color: #111; line-height: 1; font-family: 'Segoe UI', Arial, sans-serif; }
        .sl-seal .seal-sub { font-size: 4px; color: #555; letter-spacing: 1px; text-transform: uppercase; font-weight: 700; font-family: 'Segoe UI', Arial, sans-serif; }

        /* ── Hub bar ── */
        .sl-hub {
            display: flex; justify-content: space-between; align-items: center;
            padding: 2px 7px; border-bottom: 0.5px solid #bbb; background: #f5f5f5;
        }
        .sl-hub .hub-name { font-size: {{ $isRtl ? '9px' : '7.5px' }}; font-weight: 700; color: #111; }
        .sl-hub .hub-phone { font-size: 7px; color: #555; direction: ltr; }

        /* ── Reference ── */
        .sl-ref {
            display: flex; align-items: center; justify-content: space-between;
            padding: 5px 7px; border-bottom: 1px solid #ccc; background: #fafafa;
        }
        .ref-num { font-size: 13px; font-weight: 900; color: #111; letter-spacing: 1px; line-height: 1; font-family: 'Segoe UI', monospace; direction: ltr; }
        .ref-meta { font-size: {{ $isRtl ? '7px' : '6px' }}; color: #555; margin-top: 2px; }
        .ref-meta b { color: #111; }
        .ref-badges { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; }
        .badge {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 5px; border: 0.5px solid #bbb;
            font-size: 5.5px; font-weight: 700; letter-spacing: 0.5px;
            text-transform: uppercase; color: #444;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .badge.active { border-color: #2e7d32; color: #2e7d32; }
        .badge .dot { width: 4px; height: 4px; border-radius: 50%; background: currentColor; }

        /* ── Section label ── */
        .sl-sec {
            font-size: {{ $isRtl ? '8px' : '5.5px' }}; font-weight: 800; letter-spacing: {{ $isRtl ? '0' : '2px' }};
            text-transform: {{ $isRtl ? 'none' : 'uppercase' }}; color: #444;
            background: #efefef; border-top: 0.5px solid #ccc;
            border-bottom: 0.5px solid #ccc; padding: 2px 7px;
        }

        /* ── Field rows ── */
        .sl-row { display: flex; border-bottom: 0.5px solid #e8e8e8; }
        .fc { flex: 1; padding: 3px 7px 3px; border-{{ $isRtl ? 'left' : 'right' }}: 0.5px solid #e8e8e8; }
        .fc:last-child { border-{{ $isRtl ? 'left' : 'right' }}: none; }
        .fc.w2 { flex: 2; }
        .fc.w3 { flex: 3; }
        .fc .lbl {
            font-size: {{ $isRtl ? '6.5px' : '5px' }}; font-weight: 800; text-transform: {{ $isRtl ? 'none' : 'uppercase' }};
            letter-spacing: {{ $isRtl ? '0' : '1.2px' }}; color: #bbb; margin-bottom: 1px;
        }
        .fc .val { font-size: {{ $isRtl ? '9px' : '8px' }}; color: #111; font-weight: 600; line-height: {{ $isRtl ? '1.7' : '1.3' }}; }
        .fc .val.mono { font-family: 'Courier New', monospace; font-size: 7.5px; letter-spacing: 0.3px; direction: ltr; display: inline-block; }
        .fc .val.em { color: #1a2e4a; font-weight: 700; border-bottom: 1px solid #c9a227; padding-bottom: 1px; }

        /* ── Signatures ── */
        .sl-sigs { display: flex; border-top: 0.5px solid #ccc; }
        .sig { flex: 1; padding: 3px 7px 5px; border-{{ $isRtl ? 'left' : 'right' }}: 0.5px solid #e8e8e8; }
        .sig:last-child { border-{{ $isRtl ? 'left' : 'right' }}: none; }
        .sig .lbl { font-size: {{ $isRtl ? '6.5px' : '5px' }}; font-weight: 800; text-transform: {{ $isRtl ? 'none' : 'uppercase' }}; letter-spacing: {{ $isRtl ? '0' : '1px' }}; color: #bbb; margin-bottom: 10px; }
        .sig-line { border-top: 0.5px solid #bbb; }

        /* ── Footer ── */
        .sl-footer { display: flex; border-top: 1px solid #222; margin-top: auto; }
        .fc-foot { flex: 1; padding: 3px 7px; border-{{ $isRtl ? 'left' : 'right' }}: 0.5px solid #ccc; }
        .fc-foot:last-child { border-{{ $isRtl ? 'left' : 'right' }}: none; }
        .f-lbl { font-size: {{ $isRtl ? '6.5px' : '5px' }}; font-weight: 800; text-transform: {{ $isRtl ? 'none' : 'uppercase' }}; letter-spacing: {{ $isRtl ? '0' : '1.2px' }}; color: #999; margin-bottom: 1px; }
        .f-sub { font-size: {{ $isRtl ? '7px' : '5.5px' }}; color: #888; line-height: {{ $isRtl ? '1.7' : '1.3' }}; margin-bottom: 1px; }
        .f-num { font-size: 10px; font-weight: 900; color: #111; letter-spacing: 0.5px; font-family: 'Segoe UI', monospace; direction: ltr; display: inline-block; }

        /* ── Print: A6 page so 4 fit on A4 ── */
        @page { margin: 0; size: 105mm 148mm; }
        @media print {
            body { background: none; padding: 0; align-items: flex-start; }
            .no-print, .lang-btns { display: none !important; }
            .slip { width: 105mm; border: 0.5px solid #888; }
        }
    </style>
</head>
<body>

<div class="lang-btns no-print">
    <a href="?lang=en" class="{{ $lang === 'en' ? 'active' : '' }}">English</a>
    <a href="?lang=ur" class="{{ $lang === 'ur' ? 'active' : '' }}">اردو</a>
    <a href="?lang=sd" class="{{ $lang === 'sd' ? 'active' : '' }}">سنڌي</a>
</div>

<div class="no-print">
    <div class="hint">{!! $l['printHint'] !!}</div>
    <button onclick="window.history.back()"
        style="padding:6px 12px; background:#fff; color:#333; border:1px solid #bbb; font-size:11px; cursor:pointer; font-family:inherit;">
        {{ $l['back'] }}
    </button>
    <button onclick="window.print()"
        style="padding:6px 14px; background:#111; color:#fff; border:none; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit;">
        {{ $l['print'] }}
    </button>
</div>

<div class="slip">

    {{-- Header --}}
    <div class="sl-header">
        <div class="sl-las">
            <div class="las-word">LAS</div>
            <div class="las-rule"></div>
            <div class="las-sub">Legal Aid Society</div>
        </div>
        <div class="sl-center">
            <div class="eyebrow">{{ $l['clientIntake'] }}</div>
            <h1>{{ $l['justiceHub'] }}</h1>
            <div class="tagline">{{ $l['tagline'] }}</div>
        </div>
        <div class="sl-seal">
            <div class="seal-jh">JH</div>
            <div class="seal-sub">Sindh</div>
        </div>
    </div>

    {{-- Hub bar --}}
    <div class="sl-hub">
        <div class="hub-name">{{ $l['justiceHub'] }} &mdash; {{ $case->hub?->name ?? $case->hub_id }}</div>
        @if($case->hub?->phone)
        <div class="hub-phone">{{ $case->hub->phone }}</div>
        @endif
    </div>

    {{-- Reference --}}
    <div class="sl-ref">
        <div>
            <div class="ref-num">{{ $case->case_uid }}</div>
            <div class="ref-meta">
                {{ $l['date'] }}: <b>{{ $intakeDate }}</b>
                &nbsp;·&nbsp; {{ $l['staff'] }}: <b>{{ $case->staff_receiving ?? '—' }}</b>
            </div>
        </div>
        <div class="ref-badges">
            <div class="badge">
                <span class="dot" style="background:#555;"></span>
                {{ $case->hub?->district ?? $case->hub_id }}
            </div>
            <div class="badge active">
                <span class="dot"></span>
                {{ $tr(ucfirst($case->status?->value ?? 'Active')) }}
            </div>
        </div>
    </div>

    {{-- Client Info --}}
    <div class="sl-sec">{{ $l['clientInfo'] }}</div>

    <div class="sl-row">
        <div class="fc w2">
            <div class="lbl">{{ $l['fullName'] }}</div>
            <div class="val">{{ $case->name }}</div>
        </div>
        <div class="fc w2">
            <div class="lbl">{{ $l['fatherHusband'] }}</div>
            <div class="val">{{ $case->father_husband_name ?? '—' }}</div>
        </div>
        <div class="fc">
            <div class="lbl">{{ $l['gender'] }}</div>
            <div class="val">{{ $tr($case->gender) ?? '—' }}</div>
        </div>
        <div class="fc">
            <div class="lbl">{{ $l['age'] }}</div>
            <div class="val">{{ $case->age ? $case->age . 'y' : '—' }}</div>
        </div>
    </div>

    <div class="sl-row">
        <div class="fc w2">
            <div class="lbl">{{ $l['cnic'] }}</div>
            <div class="val mono">{{ $case->cnic ?? '—' }}</div>
        </div>
        <div class="fc w2">
            <div class="lbl">{{ $l['mobile'] }}</div>
            <div class="val mono">{{ $case->primary_contact ?? '—' }}</div>
        </div>
        <div class="fc w2">
            <div class="lbl">{{ $l['address'] }}</div>
            <div class="val" style="font-size:7px;">{{ $address }}</div>
        </div>
    </div>

    {{-- Pathway --}}
    <div class="sl-sec">{{ $l['pathwaySection'] }}</div>

    <div class="sl-row">
        <div class="fc">
            <div class="lbl">{{ $l['referredFrom'] }}</div>
            <div class="val">{{ $tr($case->referral_source) ?? '—' }}</div>
        </div>
        <div class="fc">
            <div class="lbl">{{ $l['pathway'] }}</div>
            <div class="val">{{ $tr($case->assigned_pathway) ?? '—' }}</div>
        </div>
        <div class="fc">
            <div class="lbl">{{ $l['specific'] }}</div>
            <div class="val">{{ $tr($case->pathway_specific) ?? '—' }}</div>
        </div>
    </div>

    <div class="sl-row">
        <div class="fc w2">
            <div class="lbl">{{ $l['assignedStaff'] }}</div>
            <div class="val em">{{ $case->assigned_to ?? '—' }}</div>
        </div>
        <div class="fc w2">
            <div class="lbl">{{ $l['staffContact'] }}</div>
            <div class="val mono">{{ $lawyerPhone ?? '—' }}</div>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="sl-sigs">
        <div class="sig">
            <div class="lbl">{{ $l['clientSig'] }}</div>
            <div class="sig-line"></div>
        </div>
        <div class="sig">
            <div class="lbl">{{ $l['staffSig'] }}</div>
            <div class="sig-line"></div>
        </div>
        <div class="sig">
            <div class="lbl">{{ $l['officeStamp'] }}</div>
            <div class="sig-line"></div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="sl-footer">
        <div class="fc-foot">
            <div class="f-lbl">{{ $l['slaccLabel'] }}</div>
            <div class="f-sub">{{ $l['slaccSub'] }}</div>
            <div class="f-num">0800-70806</div>
        </div>
        <div class="fc-foot">
            <div class="f-lbl">{{ $l['feedbackLabel'] }}</div>
            <div class="f-sub">{{ $l['feedbackSub'] }}</div>
            <div class="f-num">0345-8270806</div>
        </div>
    </div>

</div>{{-- .slip --}}

<script>
    window.addEventListener('load', function () { window.print(); });
</script>
</body>
</html>
