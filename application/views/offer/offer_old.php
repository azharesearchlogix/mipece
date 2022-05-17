<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>offer-letter</title>
    <style>
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
        #offer-letter-preview{
            width: 40%;
            border: 1px solid #cccccc;
            /* background-color: #a15252; */
            margin: 10px 0 10px 0;
            height:100%;
        }
        @media(min-width:1200px){
            #offer-letter-preview{
                width: 40%;
            }
        }
        @media(max-width:992px){
            #offer-letter-preview{
                width: 70%;
            }
        }
        @media(max-width:768px){
            #offer-letter-preview{
                width: 80%;
            }
        }
        @media(max-width:600px){
            #offer-letter-preview{
                width: 95%;
            }
            #signatures img{
                height: 50px!important;
            }
        }
    </style>
</head>
<body>
    <div id="offer-letter-preview">
        <div id="user-data" style="margin: 30px;">
            <div id="offered-user-name">
                <span style="font-size: 15px;">
                    Offer Letter of ,<strong><?php echo $result->name; ?></strong>
                </span>
            </div>
            <div id="offered-user-offer" style="margin: 30px 0 30px 0;">
                <p style="font-size: 16px;line-height: 24px;">
                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum 
                </p>
            </div>
            <div id="offered-user-joining-date" style="margin: 30px 0 20px 0;">
                <span style="font-size: 15px;line-height: 24px;">
                    <strong >Joining Date :</strong>
                    <?php echo $result->joining_date; ?>
                </span>
            </div>
            <div id="offered-user-rate" style="margin: 30px 0 20px 0;">
                <span style="font-size: 15px;line-height: 24px;">
                    <strong >Rate :</strong>
                    <?php echo '$ ' . $result->pay_rate; ?>
                </span>
            </div>
            <div id="offered-user-employment-type" style="margin: 30px 0 20px 0;">
                <span style="font-size: 15px;line-height: 24px;">
                    <strong >Employment Type :</strong>
                    <?php 
                                            if($result->emp_type==1)
                                            {
                                                echo 'Full Time';
                                            }else if($result->emp_type==2)
                                            {
                                              echo 'Contractor';  
                                            }else{
                                                echo '';
                                            }
                                            
                                            ?>
                </span>
            </div>
            <div id="offered-user-required-document" style="margin: 30px 0 20px 0;">
                <span style="font-size: 15px;line-height: 24px;">
                    <strong >Required Documents</strong> 
                    <div style="margin:10px 0 0 0">
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
                    </div>
                </span>
            </div>
            <div id="offered-user-venefits" style="margin: 30px 0 20px 0;">
                <span style="font-size: 15px;line-height: 24px;">
                    <strong >Benefits</strong> 
                    <div style="margin:10px 0 0 0">
                       
                                            <?php
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
                    </div>
                </span>
            </div>
            <div id="signatures"  style="margin: 50px 0 30px 0; display: flex;justify-content: space-around;">
                <div id="customer-sign"  style="text-align: center;">
                    <img src="<?php echo base_url($result->user_signature); ?>" style="height: 100px;" alt="customer-signature">
                    <p style="margin-top: 10px;">Customer Signature</p>
                </div>
                <div id="provider-sign" style="text-align: center;">
                    <img src="<?php echo base_url($result->provider_signature); ?>" style="height: 100px;" alt="provider-signature">
                    <p style="margin-top: 10px;">Provider Signature</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>