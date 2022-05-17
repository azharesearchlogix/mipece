
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offer Letter</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body{
            background-color: #f5f5f5;
        }
        #offer-letter-container{
            display: flex;
            justify-content: center;
        }
        #offer-letter{
            border: 1px solid #252525;
            padding: 15px;
            margin-top: 15px;
            width: 100%;
        }
        img{
            width: 100px;
        }
        @media(max-width:996px){
            #offer-letter{
                width: 80%;
            }
        }
        @media(max-width:726px){
            #offer-letter{
                width: 90%;
            }
            img{
                width: 150px;
            }
        }
    </style>
</head>
<body>
    <div id="offer-letter-container">
        <div id="offer-letter">
            <!-- logo -->
            
                <?php if(isset($result->offer_logo)):?>
                    <div align="right">
                <!--<img src="<?= base_url($result->offer_logo);?>" alt="company logo">-->
                 </div>
            <?php else:?>
               <div align="right">
                <!--<img src="<?= base_url('design/newdesign/images/logo.png');?>" alt="company logo">-->
                 </div> 
            <?php endif;?>
           
            <!-- date -->
            <div style="display: flex;justify-content: start;">
                <p><?= date('d-F-Y', strtotime($result->created_at));?></p>
            </div>

            <!-- candidate date -->
            <div style="margin-top: 20px;">
                <p><?php echo ucwords($result->name); ?></p>
                <p><?php echo ucwords($result->address); ?></p>
                <p><?php echo ucwords($result->city.','.$result->postalcode.','.$result->country); ?></p>
            </div>

            <!-- salutation -->
            <p style="margin-top: 20px;">Dear <b><?php echo ucwords($result->name); ?></b>,</p>

            <!-- body -->
            <div style="margin-top: 20px;">
                <p>
                  
                </p>
                <p style="margin-top: 20px;"><b>Agreement Details</b>

                </p><br/>
               <p> Team Name : 
                <b><?= $result->team_name;?></b></p>
                 <p style="margin-top: 10px;">No Of Team Member : 
                <b><?= $result->no_team_member;?></b></p>
                <p style="margin-top: 10px;">Team Description : 
                <b><?= $result->team_desc;?></b></p>
                <p style="margin-top: 10px;">Language : 
                <b><?= $result->lang;?></b></p>
                <p style="margin-top: 10px;">
                Job Start Date : 
                <b><?= $result->job_start_date;?></b>
                 </p>
                 <p style="margin-top: 10px;">
                Job End Date : 
                <b><?= $result->job_end_date;?></b>
                 </p>
                      <p style="margin-top: 10px;"> Project Budget : 
                <b>$<?= $result->project_budget;?></b></p>
                      <p style="margin-top: 10px;"> Payment Terms : 
                <b><?= $result->payment_mode;?></b></p>
                 <p style="margin-top: 10px;">Commission : 
                <b><?= $result->commission;?>%</b></p>
               
                <p style="margin-top: 20px;">
                    The project budget for this position is <b><?php echo '$ ' . $result->project_budget; ?></b>  to be paid on a <b><?php echo ucwords($result->payment_mode); ?></b> basis by <b>Paypal,Stripe</b> starting on on <b><?= $result->job_start_date;?></b> 
                </p>

              

              
               
                

                <p style="margin-top: 20px;">
                    Please confirm your acceptance of this agreement by signing.
                </p>

                <p style="margin-top: 20px;">
                    We are excited to have you join our team! If you have any questions, please feel free to reach out at any time.
                </p>

                <p style="margin-top: 20px;">
                    Sincerely, <br><br/>
                     <img src="<?php echo base_url($result->client_signature); ?>" style="height: 100px;" alt="client_signature">
                </p>

                <p style="margin-top: 20px;">
                   <b><?php echo ucwords($result->cname); ?></b> 
                </p>
                <p style="margin-top: 20px;">
                     <b> Signature <?php echo ucwords($result->name); ?>,</b><br/><br/>
                       <img src="<?php echo base_url($result->sc_signature); ?>"  alt="sc-signature"> <br>
                </p>

              
            </div>
        </div>
    </div>
</body>
</html>