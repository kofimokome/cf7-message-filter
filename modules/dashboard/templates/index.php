<?php
namespace km_message_filter;
$upgrade_url       = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-pricing';
$upgrade_guide_url = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-options&tab=upgrade';
?>

<style>
    .card {
        max-width: 100%;
    }
</style>
<div id="wrapper">

    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->

    <div class="content-page" style="margin-top:0; margin-left:0;">
        <div class="content">
            <!--<div class="alert alert-info alert-dismissible">
				<p>Hello There!. Thank for using this plugin. It will be great if you can take just 5 minutes of your
					time to leave a review<br>
					<a href="https://wordpress.org/support/plugin/cf7-message-filter/reviews/#new-post"
					   class="btn btn-success" target="_blank" rel="noopener noreferrer">Submit Reveiw</a>
				</p>
			</div>-->
            <div class="container-fluid">
                <div class="row page-title align-items-center">
                    <div class="col-sm-4 col-xl-6">
                        <h4 class="mb-1 mt-0">Message Filter for Contact Form 7
                            v.<?php echo KMCFMessageFilter::getVersion() ?>
                        </h4>
                    </div>
                </div>
                <div class="row">
                    <form action="https://ko-fi.com/kofimokome" method="post" target="_blank">
                        <input type="hidden" name="hosted_button_id" value="B3JAV39H95RFG"/>
                        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit"
                               title="Ko-fi is the easiest way for you to start making an income directly from your fans" alt="Donate with PayPal button"/>
                        <img alt="" border="0" src="https://www.paypal.com/en_CM/i/scr/pixel.gif" width="1" height="1"/>
                    </form>
                </div>

                <!-- stats + charts -->
                <div class="row">
                    <div class="col-xl-5">
                        <div class="card">
                            <div class="card-body p-0">
                                <h5 class="card-title header-title border-bottom p-3 mb-0"><?php esc_html_e( "Overview", KMCF7MS_TEXT_DOMAIN ) ?></h5>

                                <div class="row"><!-- stat 1 -->
                                    <div class="col-6 media px-3 py-4 border-bottom border-right">
                                        <div class="media-body">
                                            <h4 class="mt-0 mb-1 font-size-22 font-weight-normal"><?php echo get_option( 'kmcfmf_messages_blocked' ); ?></h4>
                                            <span class="text-muted"><?php esc_html_e( "Messages Blocked", KMCF7MS_TEXT_DOMAIN ) ?></span>
                                        </div>
                                        <!--                                    <i data-feather="users" class="align-self-center icon-dual icon-lg"></i>-->
                                    </div>

                                    <!-- stat 2 -->
                                    <div class="col-6 media px-3 py-4 border-bottom">
                                        <div class="media-body">
                                            <h4 class="mt-0 mb-1 font-size-22 font-weight-normal"><?php echo get_option( 'kmcfmf_messages_blocked_today' ); ?></h4>
                                            <span class="text-muted"> <?php esc_html_e( "Blocked Today", KMCF7MS_TEXT_DOMAIN ) ?></span>
                                        </div>
                                        <!--                                    <i data-feather="image" class="align-self-center icon-dual icon-lg"></i>-->
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- stats + charts -->
                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body pb-0">
                                <h5 class="card-title mb-0 header-title"><?php esc_html_e( "Weekly Statistics", KMCF7MS_TEXT_DOMAIN ) ?></h5>

                                <div id="stats-chart" class="apex-charts mt-3" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body pb-0">
                                <h5 class="card-title mb-0 header-title"><?php esc_html_e( "Word Frequency", KMCF7MS_TEXT_DOMAIN ) ?></h5>

                                <div id="words-chart" class="apex-charts mt-3" dir="ltr"></div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- row -->
            </div>
        </div> <!-- content -->

    </div>
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="exampleModalLabel"><?php esc_html_e( "Thank You For Choosing Contact Form 7 Filter", KMCF7MS_TEXT_DOMAIN ) ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert border-success">
						<?php esc_html_e( " It will be great if you can take just 5 minutes of your
                        time to leave a review, if this plugin has been useful to you", KMCF7MS_TEXT_DOMAIN ) ?><br>
                        <a href="https://wordpress.org/support/plugin/cf7-message-filter/reviews/#new-post"
                           class="btn btn-success" target="_blank"
                           rel="noopener noreferrer"><?php esc_html_e( "Submit Review", KMCF7MS_TEXT_DOMAIN ) ?></a>
                        <a href="https://ko-fi.com/kofimokome"
                           class="btn btn-primary" target="_blank"
                           rel="noopener noreferrer"><?php esc_html_e( "Buy me Coffee", KMCF7MS_TEXT_DOMAIN ) ?></a>

                    </div>
                    <h5> Here are a few changes in this version:</h5>
                    <ol>
                        <li>You can now mark a blocked message as not spam (works only for Contact Form 7)</li>
                    </ol>
					<?php esc_html_e( "Please help translate this plugin to your language", KMCF7MS_TEXT_DOMAIN ) ?> <a
                            href="https://translate.wordpress.org/projects/wp-plugins/cf7-message-filter/"
                            target="_blank"
                            class="btn btn-sm btn-primary"><?php esc_html_e( "Translate Now", KMCF7MS_TEXT_DOMAIN ) ?></a>
                    <br>
					<?php _e( 'If you find an issue, please <a href="https://wordpress.org/support/plugin/cf7-message-filter/"
                                                    target="_blank">create a support ticket here</a> and I will do my
                    best to fix as soon as possible', KMCF7MS_TEXT_DOMAIN ) ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal"><?php esc_html_e( "Close", KMCF7MS_TEXT_DOMAIN ) ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->


</div>
<?php $word_options = json_encode( array_keys( json_decode( get_option( 'kmcfmf_word_stats' ), true ) ) );
$words              = "[";
foreach ( json_decode( get_option( 'kmcfmf_word_stats' ), true ) as $word ) {
	$words .= $word . ",";
}
$words .= "]";
?>
<script>
    // start of transaction charts
    var options = {
        series: [{
            name: 'Messages blocked',
            data: <?php echo get_option( 'kmcfmf_weekly_stats' )?>,
        }],
        chart: {
            height: 350,
            type: 'line',
        },
        stroke: {
            width: 7,
            curve: 'smooth'
        },
        xaxis: {
            type: 'text',
            categories: ['MON', 'TES', 'WED', 'THUR', 'FRI', 'SAT', 'SUN'],
        },
        title: {
            text: '',
            align: 'left',
            style: {
                fontSize: "16px",
                color: '#666'
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                gradientToColors: ['#FDD835'],
                shadeIntensity: 1,
                type: 'horizontal',
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100, 100, 100]
            },
        },
        markers: {
            size: 4,
            colors: ["#FFA41B"],
            strokeColors: "#fff",
            strokeWidth: 2,
            hover: {
                size: 7,
            }
        },
        yaxis: {
            //min: -10,
            // max: 40,
            title: {
                text: 'Messages blocked',
            },
        }
    };

    var chart = new ApexCharts(document.querySelector("#stats-chart"), options);
    chart.render();

    var word_options = {
        series: <?php echo $words?>,
        chart: {
            width: 380,
            type: 'pie',
        },
        labels: <?php echo $word_options?>,
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 350
                },
                legend: {
                    position: 'top'
                }
            }
        }]
    };

    var word_chart = new ApexCharts(document.querySelector("#words-chart"), word_options);
    word_chart.render();
	<?php if(get_option( 'kmcfmf_version', '0' ) != KMCFMessageFilter::getVersion()):?>
    jQuery(document).ready(function ($) {
        $('#myModal').modal()
    });
	<?php update_option( 'kmcfmf_version', KMCFMessageFilter::getVersion() );endif;?>
</script>
<!-- END wrapper -->