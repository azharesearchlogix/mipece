<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Invoice</title>
  <style type="text/css">
       *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body{
            display: flex;
            justify-content: center;
            font-family:  Arial, sans-serif;
        }
        #invoice-preview{
            width: 60%;
            margin: 10px 0 10px 0;
            height:100%;
        }
        #invoice-data{
            border: 1px solid #0D83DD;
            background-color: #F5FBFE;
        }
        p,h1{
            margin-top: 10px;
        }
        small{
            margin-bottom: 10px;
            color: #8F8A7C;
        }
        .total-amount h1{
            font-size: 38px;
        }
        table{
            width: 100%;
        }
        th{
            color: #0D83DD;
            font-size: 14px;
        }
        th,td{
            text-align: center;
        }
        #invoice-logo img{
            width: 30%;
        }
        #invoice-text{
            font-size: 54px;
        }
        p{
            font-size: 14px;
        }
        @media(max-width:1150px){
            th,td{
                font-size: 13px;
            }
        }
        @media(max-width:998px){
            #invoice-preview{
                width: 75%;
            }
            #invoice-text{
                font-size: 44px;
            }
        }
        @media(max-width:798px){
            #invoice-preview{
                width: 90%;
            }
            #invoice-logo img{
                width: 50%;
            }
            #invoice-text{
                font-size: 34px;
            }
        }
        @media(max-width:690px){
            th,td{
                font-size: 12px;
            }
            #invoice-text{
                font-size: 28px;
            }
        }
        #total-amount{
            text-align: right;
        }
        @media(max-width:450px){
            #invoice-preview{
                width: 96%;
            }
            th,td{
                font-size: 10px;
            }
            #invoice-text{
                font-size: 24px;
            }
            #sp-details p{
                font-size: 12px;
            }
            
        }
        #billing-details{
            justify-content: space-between;
        }
        @media(max-width:400px){
            #total-amount{
                text-align: center;
            }
            #billing-details{
                justify-content: space-around;
            }
        }
  </style>
</head>
<body>
    <div id="invoice-preview">
      <div id="invoice-data" style="padding: 10px;">

        <table style="width: 100%;">
            <tr>
                <td style="text-align: center;">
                    <img src="https://esldevstudio.com/app/myteam/design/newdesign/images/logo.png" alt="mipece logo" style="width: 50%;">
                </td>
            </tr>
        </table>

        <table style="background-color: #0D83DD;color: #fefefe; padding: 10px;">
            <tr>
                    <td>
                        <p style="font-size: 2.3rem;text-align: left;">
                            INVOICE
                        </p>
                    </td>
                    <td style="text-align: right;">
                        <h3><?= ucwords($data->spname);?></h3>
                        <h3><?= $data->sp_contact;?></h3>
                    </td>
            </tr>
        </table>

        <table style="margin-top: 35px;">
            <tr style="vertical-align: top;">
                <td style="text-align: left;">
                        <small> <b>Billed To</b></small>
                        <p id="client-name"><?= ucwords($data->client_name);?></p>
                        <p id="client-address"><?= $data->client_address;?></p>
                        <p id="client-address"><?= $data->client_city.','.$data->client_country;?></p>
                        <p id="client-address"><?= $data->client_postalcode;?></p>
                </td>
                <td style="text-align: left;">
                        <div id="invoice-number">
                            <small> <b>Invoice Number</b></small>
                            <p id="in-number">00000000<?= $data->id;?></pdiv>
                        </div>

                        <div id="invoice-date" style="margin-top: 20px;">
                            <small> <b>Date of Issue</b></small>
                            <p id="date-of-issue"><?= date('d-m-Y', strtotime($data->date));?></p>
                        </div>
                </td>
                <td style="text-align: right;">
                        <small> <b>Invoice Total</b></small>
                        <h1 style="color: #0D83DD;">$<?= number_format($data->amount,2);?></h1>
                </td>
            </tr>
        </table>

        <div style="width: 100%;height: 3px; background-color: #0D83DD; margin: 30px 0;"></div>

        <table style="margin: 10px 0;" >
                <tr style="vertical-align: top;">
                    <th style="text-align: left;">Task Name</th>
                    <th>Task Assigned Data</th>
                    <th>Task Start Time</th>
                    <th>Task End Time</th>
                    <th>Cost</th>
                </tr>
                <tr>
                    <td style="text-align: left;">
                        <p><?= ucwords($data->task_name);?></p>
                        <small><?= $data->description;?></small>
                    </td>
                    <td><?= date('d-m-Y', strtotime($data->taskdate));?></td>
                    <td><?= date('H:i', strtotime($data->start_time));?></td>
                    <td><?= date('H:i', strtotime($data->end_time));?></td>
                    <td>$<?= number_format($data->amount,2);?></td>
                </tr>
            </table>

        <div style="width: 100%;height: 3px; background-color: #0D83DD; margin: 30px 0;"></div>


        <table style="margin: 10px 0;">
                <tr>
                    <td style="text-align: left;">
                        <div>
                            <small>Invoice Generated By</small>
                            <p><?= ucwords($data->spname);?></p>
                        </div>
                        
                    </td>
                    <td >
                        <div>
                            <p style="text-align: right;">
                                <span style="color: #0D83DD;margin-right: 10px;">Total</span>
                                <span>$<?= number_format($data->amount,2);?></span>
                            </p>
                        </div>
                    </td>
                </tr>
        </table>
       </div>

    </div>
</body>
</html>