<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
	<head>
		<title></title>
		<!--[if !mso]><!-- -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<!--<![endif]-->
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<style type="text/css">
			#outlook a { padding: 0; }
			.ReadMsgBody { width: 100%; }
			.ExternalClass { width: 100%; }
			.ExternalClass * { line-height:100%; }
			body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
			table, td { border-collapse:collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
			img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
			p { display: block; margin: 13px 0; }
		</style>
		<!--[if !mso]><!-->
		<style type="text/css">
			@media only screen and (max-width:480px) {
			@-ms-viewport { width:320px; }
			@viewport { width:320px; }
			}
		</style>
		<!--<![endif]-->
		<!--[if mso]>
		<xml>
			<o:OfficeDocumentSettings>
				<o:AllowPNG/>
				<o:PixelsPerInch>96</o:PixelsPerInch>
			</o:OfficeDocumentSettings>
		</xml>
		<![endif]-->
		<!--[if lte mso 11]>
		<style type="text/css">
			.outlook-group-fix {
			width:100% !important;
			}
		</style>
		<![endif]-->
		<!--[if !mso]><!-->
		<link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
		<style type="text/css">
			@import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
		</style>
		<!--<![endif]-->
		<style type="text/css">
			@media only screen and (min-width:480px) {
			.mj-column-per-100 { width:100%!important; }
			}
		</style>
	</head>
	<body style="background: #eee;">
		<div class="mj-container" style="background-color:#eee;">
			<!--[if mso | IE]>
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
				<tr>
					<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
						<![endif]-->
						<div style="margin:0px auto;max-width:600px;">
							<table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;" align="center" border="0">
								<tbody>
									<tr>
										<td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:20px 0px;">
											<!--[if mso | IE]>
											<table role="presentation" border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td style="vertical-align:top;width:600px;">
														<![endif]-->
														<div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
															<table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
																<tbody>
																	<tr>
																		<td style="word-wrap:break-word;font-size:0px;padding:10px 25px;" align="center">
																			<div style="cursor:auto;color:#555;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;text-align:center;"><img src="{{ url('/img-emails/'.$platform.'.png') }}"></div>
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>
														<!--[if mso | IE]>
													</td>
												</tr>
											</table>
											<![endif]-->
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<!--[if mso | IE]>
					</td>
				</tr>
			</table>
			<![endif]-->


			<!--[if mso | IE]>
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
				<tr>
					<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
						<![endif]-->
						<div style="margin:0px auto;border-radius:5px;max-width:600px;background:#fff;">
							<table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;border-radius:5px;background:#fff;" align="center" border="0">
								<tbody>
									<tr>
										<td style="text-align:center;vertical-align:top;border:1px solid #f5f8fa;direction:ltr;font-size:0px;padding:20px 0px;">
											<!--[if mso | IE]>
											<table role="presentation" border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td style="vertical-align:top;width:600px;">
														<![endif]-->
														<div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
															<table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
																<tbody>
																	<tr>
																		<td style="word-wrap:break-word;font-size:0px;padding:10px 25px;" align="center">
																			<div style="cursor:auto;color:#555;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;text-align:center;">
																					@if($title)
																						<h1 style="font-size: 16px;">{{ $title }}</h1>
																					@endif
																					@if($subtitle)
																						<h2 style="font-size: 14px;">{{ $subtitle }}</h2>
																					@endif
																			</div>
																		</td>
																	</tr>
																	<tr>
																		<td style="word-wrap:break-word;font-size:0px;padding:10px 25px;" align="left">
																			<div style="cursor:auto;color:#555;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;text-align:left;">
																					{!! stripslashes(nl2br($content)) !!}
																			</div>
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>
														<!--[if mso | IE]>
													</td>
												</tr>
											</table>
											<![endif]-->
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<!--[if mso | IE]>
					</td>
				</tr>
			</table>
			<![endif]-->

			<!--[if mso | IE]>
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
				<tr>
					<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
						<![endif]-->
						<div style="margin:0px auto;max-width:600px;">
							<table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;" align="center" border="0">
								<tbody>
									<tr>
										<td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:20px 0px;">
											<!--[if mso | IE]>
											<table role="presentation" border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td style="vertical-align:top;width:600px;">
														<![endif]-->
														<div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
															<table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
																<tbody>
																	<tr>
																		<td style="word-wrap:break-word;font-size:0px;padding:10px 25px;" align="center">
																			<div style="cursor:auto;color:#555;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;text-align:center;">Follow us on:
																				<a href="https://www.facebook.com/DentaVox-1578351428897849/">Facebook</a> |
																				<a href="https://t.me/dentacoin">Telegram</a>
																			</div>
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>
														<!--[if mso | IE]>
													</td>
												</tr>
											</table>
											<![endif]-->
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<!--[if mso | IE]>
					</td>
				</tr>
			</table>
			<![endif]-->
			@if(!empty($unsubscribe))
				<!--[if mso | IE]>
				<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
					<tr>
						<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
							<![endif]-->
							<div style="margin:0px auto;max-width:600px;">
								<table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;" align="center" border="0">
									<tbody>
										<tr>
											<td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;">
												<!--[if mso | IE]>
												<table role="presentation" border="0" cellpadding="0" cellspacing="0">
													<tr>
														<td style="vertical-align:top;width:600px;">
															<![endif]-->
															<div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
																<table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
																	<tbody>
																		<tr>
																			<td style="word-wrap:break-word;font-size:0px;" align="center">
																				<div style="cursor:auto;color:#555;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;text-align:center;">
																					<a href="{{ $unsubscribe }}">{{ trans('front.page.emails.unsubscribe') }}</a>
																				</div>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</div>
															<!--[if mso | IE]>
														</td>
													</tr>
												</table>
												<![endif]-->
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<!--[if mso | IE]>
						</td>
					</tr>
				</table>
				<![endif]-->
			@endif
			<!--[if mso | IE]>
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
				<tr>
					<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
						<![endif]-->
						<div style="margin:0px auto;max-width:600px;">
							<table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;" align="center" border="0">
								<tbody>
									<tr>
										<td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:20px 0px;">
											<!--[if mso | IE]>
											<table role="presentation" border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td style="vertical-align:top;width:600px;">
														<![endif]-->
														<div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
															<table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
																<tbody>
																	<tr>
																		<td style="word-wrap:break-word;font-size:0px;padding:10px 25px;" align="center">
																			<div style="cursor:auto;color:#555;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;text-align:center;">
																				{{ trans('front.page.emails.footer') }}
																			</div>
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>
														<!--[if mso | IE]>
													</td>
												</tr>
											</table>
											<![endif]-->
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<!--[if mso | IE]>
					</td>
				</tr>
			</table>
			<![endif]-->
			<!--[if mso | IE]>
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
				<tr>
					<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
						<![endif]-->
						<div style="margin:0px auto;max-width:600px;">
							<table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;" align="center" border="0">
								<tbody>
									<tr>
										<td style="text-align:center;vertical-align:top;direction:ltr;font-size:0px;padding:20px 0px;">
											<!--[if mso | IE]>
											<table role="presentation" border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td style="vertical-align:top;width:600px;">
														<![endif]-->
														<div class="mj-column-per-100 outlook-group-fix" style="vertical-align:top;display:inline-block;direction:ltr;font-size:13px;text-align:left;width:100%;">
															<table role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
																<tbody>
																	<tr>
																		<td style="word-wrap:break-word;font-size:0px;padding:10px 25px;" align="center">
																			<div style="cursor:auto;color:#555;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;text-align:center;">
																				{!! nl2br(trans('front.page.emails.copyrights')) !!}
																			</div>
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>
														<!--[if mso | IE]>
													</td>
												</tr>
											</table>
											<![endif]-->
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<!--[if mso | IE]>
					</td>
				</tr>
			</table>
			<![endif]-->
		</div>
	</body>
</html>