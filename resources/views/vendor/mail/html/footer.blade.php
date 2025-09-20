<tr>
<td style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center" style="padding: 30px 20px;">
{{ Illuminate\Mail\Markdown::parse($slot) }}

{{-- Custom SIMS Footer --}}
<div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #718096; font-size: 12px; line-height: 1.5;">
<p style="margin: 0 0 10px 0;">
<strong>USTP Panaon Supply Office</strong><br>
Supply Office Inventory Management System (SIMS)
</p>
<p style="margin: 0 0 10px 0;">
📧 <a href="mailto:supply.office@panaon.ustp.edu.ph" style="color: #4a5568; text-decoration: none;">supply.office@panaon.ustp.edu.ph</a><br>
📍 USTP Panaon Campus, Panaon, Zamboanga del Norte
</p>
<p style="margin: 0; color: #a0aec0; font-size: 11px;">
© {{ date('Y') }} University of Science and Technology of Southern Philippines. All rights reserved.<br>
This is an automated message. Please do not reply to this email.
</p>
</div>
</td>
</tr>
</table>
</td>
</tr>
