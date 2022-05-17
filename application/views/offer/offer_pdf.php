
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
             <?php if($result->offer_logo):?>
                    <div align="right">
                <img src="<?= base_url($result->offer_logo);?>" alt="Logo" height="100" width="100">
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
            <p style="margin-top: 20px;">Dear <?php echo ucwords($result->name); ?>,</p>

            <!-- body -->
            <div style="margin-top: 20px;">
                <p>
                    We are pleased to offer you the  <?php 
                                            if($result->emp_type==1)
                                            {
                                                echo '<b>Full Time</b>';
                                            }else if($result->emp_type==2)
                                            {
                                              echo '<b>Contractor</b>';  
                                            }else{
                                                echo '';
                                            }
                                            
                                            ?> <!--position of [job title]--> at <b><?php echo ucwords($result->teamname); ?></b> with a start date of <b><?php echo $result->joining_date; ?></b>, contingent upon [background check, I-9 form, etc.]. 
                    You will be reporting directly to <b><?php echo ucwords($result->cname); ?></b> at <b><?php echo ucwords($result->caddress); ?></b>. We believe your skills and experience are an excellent match for our company.
                </p>
                <p style="margin-top: 20px;">In this role, you will be required to 

                </p><br/><b>
                 <?php
                                            $i = 1;
                                            foreach ($requiredoc as $key => $value) {
                                                ?>
                                                <p>
                                                    <?php echo $i . '. ' . $value->title; ?>
                                                </p>
                                                <?php
                                                $i++;
                                            }
                                            ?>
                                        </b>
                <p style="margin-top: 20px;">
                    The hourly salary for this position is <b><?php echo '$ ' . $result->pay_rate; ?></b>  to be paid on a <b><?php echo ucwords($result->pmode); ?></b> basis by <b>Paypal,Stripe</b> starting on on <b><?= date('d-F-Y', strtotime($result->created_at));?></b> 
                </p>

                <p style="margin-top: 20px;">
                    Your employment with <b><?php echo ucwords($result->teamname); ?></b> will be on an at-will basis, which means you and the company are free to terminate the employment relationship at any time for any reason. This letter is not a contract or guarantee of employment for a definitive period of time.
                </p>

                <p style="margin-top: 20px;">
                    As an employee of <b><?php echo ucwords($result->teamname); ?></b>, you are also eligible for our benefits program, which includes <br/><br/>
                     <b><?php
                                            if($benifits){
                                            $i = 1;
                                            foreach ($benifits as $key => $value) {
                                                ?>
                                                <p >
                                                    <?php echo $i . '. ' . $value->title; ?>
                                                </p>
                                                <?php
                                                $i++;
                                            }
                                            }else{
                                                echo 'No Benefits available';
                                            }
                                            ?>
                                        </b>
                </p>
               
                

                <p style="margin-top: 20px;">
                    Please confirm your acceptance of this offer by signing.
                </p>

                <p style="margin-top: 20px;">
                    We are excited to have you join our team! If you have any questions, please feel free to reach out at any time.
                </p>

                <p style="margin-top: 20px;">
                    Sincerely, <br><br/>
                     <img src="<?php echo base_url($result->user_signature); ?>" style="height: 100px;" alt="customer-signature">
                </p>

                <p style="margin-top: 20px;">
                   <b><?php echo ucwords($result->cname); ?></b> <br>
                    <?php echo ucwords($result->teamname); ?>
                </p>

                <p style="margin-top: 20px;">
                    Signature,<br/><br/>
                       <img src="<?php echo base_url($result->provider_signature); ?>"  alt="provider-signature"> <br>
                    Name: <b><?php echo ucwords($result->name); ?></b> <br>
                    Date: <b><?= date('d-F-Y', strtotime($result->created_at));?></b>
                </p>
            </div>
        </div>
    </div>
</body>
</html>