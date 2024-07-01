<?php
$settings_url = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-options&tab=data_collection';

$words = json_decode( get_option( 'kmcfmf_word_stats' ), true );

function myf( $a, $b ) {
	return $a - $b;
}

uasort( $words, 'myf' );
$words = array_reverse( $words );
$keys  = array_slice( array_keys( $words ), 0, 5 );
$user  = get_option( 'admin_name' );

?>

<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <title>Simple Transactional Email </title>
</head>
<body>
<div class="container">
    <div class="row" style="background: #eee">
        <div class="col-md-7 mx-auto d-flex justify-content-center flex-column py-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-center">Message Filter For Contact Form 7 Report</h4>
                    <div> Hello, <br/>Thank you for using Message Filter For Contact Form 7.<br/>Here is a summary of
                        how we protected your website from spam messages in the last 30 days.
                    </div>
                    <div class="mt-2">
                        <img class="img-fluid"
                             src='https://quickchart.io/chart?c={type:"bar",data:{labels:%s,datasets:[{label:"Messages Blocked",data:%s}]}}'/>
                    </div>
                    <div class="mt-2">
                        <h5>Most used spam words</h5>
                        <table class="table table-striped">
                            <tr>
                                <th>Word</th>
                                <th>Times used</th>
                            </tr>
							<?php foreach ( $keys as $key ): ?>
                                <tr>
                                    <td><?php echo $key ?></td>
                                    <td>
                                        <div class="badge bg-primary"> <?php echo $words[ $key ] ?></div>
                                    </td>
                                </tr>
							<?php endforeach; ?>

                        </table>
                    </div>
                    <div class="mt-3">
                        <strong>Kofi Mokome</strong> <br/>
                        <span>Support Team</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 small">
                <hr/>
                <i>Do you want to stop receiving this email? Deactivate it on the <a href="<?php echo $settings_url ?>">plugin
                        settings
                        page</a></i>
            </div>
        </div>
    </div>
</div>
</body>
</html>