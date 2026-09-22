<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo e($mailSubject); ?></title>
    <style>
        @media only screen and (max-width:600px) {
            .email-outer { padding:16px 8px !important; }
            .email-content { padding:26px 22px !important; }
            .email-header { padding:26px 22px !important; }
            .email-title { font-size:25px !important; }
            .detail-label,.detail-value { display:block !important; width:auto !important; }
            .detail-label { padding-bottom:3px !important; }
            .detail-value { padding-top:0 !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#eef3f8;color:#18324d;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%">
<div style="<?php echo \Illuminate\Support\Arr::toCssStyles(['display:none;font-size:1px;line-height:1px;color:#eef3f8;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all']) ?>"><?php echo e($heading); ?> · Mediland Hospital</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef3f8">
    <tr><td class="email-outer" align="center" style="padding:40px 16px">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background-color:#ffffff;border:1px solid #dce5ef;border-radius:16px;border-spacing:0;overflow:hidden">
            <tr><td style="height:5px;line-height:5px;background-color:#16a6a1;border-radius:16px 16px 0 0">&nbsp;</td></tr>
            <tr><td class="email-header" style="padding:30px 36px;background-color:#0b355d;color:#ffffff">
                <p style="margin:0;font-size:23px;line-height:1.3;font-weight:700;letter-spacing:2px">MEDICARE</p>
                <p style="margin:7px 0 0;font-size:12px;line-height:1.6;color:#c6d9e9">Mediland Hospital OPD &middot; Kalmunai</p>
            </td></tr>
            <tr><td class="email-content" style="padding:34px 36px">
                <p style="margin:0 0 10px;font-size:10px;line-height:1.5;font-weight:700;letter-spacing:1.8px;color:#087e83;text-transform:uppercase">Mediland Hospital</p>
                <h1 class="email-title" style="margin:0 0 24px;font-size:28px;line-height:1.25;font-weight:700;letter-spacing:-.5px;color:#102d4e"><?php echo e($heading); ?></h1>
                <?php if($greeting): ?>
                    <p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#18324d"><?php echo e($greeting); ?></p>
                <?php endif; ?>
                <?php if($code): ?>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;background-color:#f0f6fc;border:1px solid #d5e5f5;border-radius:12px">
                        <tr><td align="center" style="padding:22px 16px">
                            <p style="margin:0 0 10px;font-size:11px;line-height:1.5;font-weight:700;letter-spacing:1.5px;color:#57718b">YOUR ONE-TIME CODE</p>
                            <p style="margin:0;font-family:Consolas,'Courier New',monospace;font-size:34px;line-height:1.4;font-weight:700;letter-spacing:7px;color:#0b5dcc"><?php echo e($code); ?></p>
                        </td></tr>
                    </table>
                <?php endif; ?>
                <?php $__currentLoopData = $paragraphs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paragraph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p style="margin:0 0 16px;font-size:14px;line-height:1.75;color:#4b6279"><?php echo e($paragraph); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($details): ?>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" aria-label="Message details" style="margin:22px 0;background-color:#f6f9fc;border:1px solid #e1e9f2;border-radius:10px">
                        <?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <th class="detail-label" scope="row" width="35%" align="left" valign="top" style="<?php echo \Illuminate\Support\Arr::toCssStyles(['padding:12px 16px;font-size:12px;line-height:1.6;font-weight:400;color:#61768a;', 'border-bottom:1px solid #e1e9f2' => !$loop->last]) ?>"><?php echo e($detail['label']); ?></th>
                                <td class="detail-value" valign="top" style="<?php echo \Illuminate\Support\Arr::toCssStyles(['padding:12px 16px;font-size:13px;line-height:1.6;font-weight:700;color:#18324d;overflow-wrap:anywhere;', 'border-bottom:1px solid #e1e9f2' => !$loop->last]) ?>"><?php echo e($detail['value']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </table>
                <?php endif; ?>
                <?php if($actionText && $actionUrl): ?>
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0">
                        <tr><td align="center" bgcolor="#0b5dcc" style="<?php echo \Illuminate\Support\Arr::toCssStyles(['border-radius:8px;mso-padding-alt:14px 24px']) ?>">
                            <a href="<?php echo e($actionUrl); ?>" style="display:inline-block;padding:14px 24px;border:1px solid #0b5dcc;border-radius:8px;font-size:14px;line-height:1.4;font-weight:700;color:#ffffff;text-decoration:none"><?php echo e($actionText); ?></a>
                        </td></tr>
                    </table>
                <?php endif; ?>
                <p style="margin:26px 0 0;padding-top:20px;border-top:1px solid #e5ecf3;font-size:13px;line-height:1.7;color:#61768a">Kind regards,<br><strong style="color:#18324d">Mediland Hospital Team</strong></p>
            </td></tr>
            <tr><td align="center" style="padding:20px 24px;background-color:#f8fafc;border-top:1px solid #e5ecf3;border-radius:0 0 16px 16px">
                <p style="margin:0;font-size:11px;line-height:1.8;color:#73869a">MEDICARE &middot; Mediland Hospital &middot; Kalmunai, Sri Lanka</p>
                <p style="margin:4px 0 0;font-size:11px;line-height:1.8;color:#73869a">Compassionate care, connected around you.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html><?php /**PATH C:\xampp\htdocs\medicare\resources\views\emails\hospital-message.blade.php ENDPATH**/ ?>