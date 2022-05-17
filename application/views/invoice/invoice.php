

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Mipece team invoice</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <style type="text/css">
            a[x-apple-data-detectors] {color: inherit !important;}
        </style>

    </head>
    <body style="margin: 0; padding: 0;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding: 20px 0 30px 0;">

                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; border: 1px solid #cccccc;">

                        <tr>
                            <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">


                                    <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif;">
                                            <h2 style=" margin: 0; text-align: center;"><?= ucwords($invoice->name);?></h2>
                                            <h4 align="center"><?= date('d-m-Y',strtotime($invoice->created_at));?></h4>
                                        </td>
                                    </tr>                                   

                                    <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding: 20px 0 30px 0;">
                                            <p style="margin: 0;"> 
                                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 15px; padding: 20px 0 20px 0;">
                                            <h1 style="font-size: 15px; margin: 0;">Invoice No: <?= $invoice->invoice_id;?></h1>
                                        </td>
                                    </tr>
                                    
                                     <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 15px; padding: 20px 0 20px 0;">
                                            <h1 style="font-size: 15px; margin: 0;">Team Name: <?php echo  ucwords($invoice->teamname); ?></h1>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif;">
                                            <h1 style="font-size: 15px; margin: 0;">Service Provider Name: <?= ucwords($sp->firstname.' '.$sp->lastname);?></h1>
                                        </td>
                                    </tr> 

                                    <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; padding: 20px 0 30px 0; line-height: 30px;">
                                           
                                           <h1 style="font-size: 15px; margin: 0;">Leave Balance: <?= $invoice->leave_balance;?></h1>


                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif; line-height: 30px;">
                                            <h1 style="font-size: 15px; margin: 0;">Leave Taken: <?= $invoice->leave_taken;?></h1>
                                        </td>
                                    </tr> 
                                      <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif; line-height: 30px;">
                                            <h1 style="font-size: 15px; margin: 0;">Total Work Hours: <?= $invoice->total_work_hours;?> Hours</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif; line-height: 30px;">
                                            <h1 style="font-size: 15px; margin: 0;">Work Hours Rate: <?= $invoice->rate;?></h1>
                                        </td>
                                    </tr> 
                                      <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif; line-height: 30px;">
                                            <h1 style="font-size: 15px; margin: 0;">Invoice Amount: <?= $invoice->amount;?></h1>
                                        </td>
                                    </tr> 
                                     <tr>
                                        <td style="color: #153643; font-family: Arial, sans-serif; line-height: 30px;">
                                            <h1 style="font-size: 15px; margin: 0;">Invoice Status: <?= ($invoice->paid_status=='0')?'<font color="red"><b>Pending</b></font>':'<font color="green"><b>Paid</b></font>';?></h1>
                                        </td>
                                    </tr> 

                                    

                                  

                                </table>
                            </td>
                        </tr>

                    </table>

                </td>
            </tr>
        </table>
    </body>
</html>

