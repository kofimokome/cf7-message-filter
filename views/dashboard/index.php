<?php
namespace km_message_filter;
$upgrade_url       = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-pricing';
$upgrade_guide_url = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-options&tab=upgrade';
$messages_url      = admin_url( 'admin.php' ) . '?page=kmcf7-filtered-messages';
$settings_url      = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-options';
update_option( "kmcfmf_messages_blocked_today_tmp", 0 );
$frequent_words  = StatisticsModule::getInstance()->frequentWords();
$frequent_emails = StatisticsModule::getInstance()->frequentEmails();

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
                            v.<?php echo KMCFMessageFilter::getInstance()->getVersion() ?>

							<?php if ( kmcf7ms_fs()->is_premium() ): ?>
                                pro
							<?php else: ?>
                                free
							<?php endif; ?>
                        </h4>
                    </div>
                </div>
				<?php if ( kmcf7ms_fs()->is_free_plan() ): ?>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="alert alert-danger">
                                <h4 style="color:white;">
                                    You are using the free plugin.
                                    <a class="btn btn-primary" href="<?php echo $upgrade_url ?>"> Upgrade </a> to
                                    unlock all features
                                </h4>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>
				<?php if ( kmcf7ms_fs()->can_use_premium_code() ): ?>
					<?php if ( ! kmcf7ms_fs()->is_premium() ): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h4 style="color:white;">
                                        Looks like you have a premium license. 🤔 Please check the
                                        <a class="btn btn-primary" href="<?php echo $upgrade_guide_url ?>"> Upgrade
                                            Guide </a>
                                    </h4>
                                </div>
                            </div>
                        </div>
					<?php endif; ?>
				<?php endif; ?>
                <!--  <div class="row">
					  <form action="https://ko-fi.com/kofimokome" method="post" target="_blank">
						  <input type="hidden" name="hosted_button_id" value="B3JAV39H95RFG"/>
						  <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"
								 border="0" name="submit"
								 title="Ko-fi is the easiest way for you to start making an income directly from your fans"
								 alt="Donate with PayPal button"/>
						  <img alt="" border="0" src="https://www.paypal.com/en_CM/i/scr/pixel.gif" width="1" height="1"/>
					  </form>
				  </div>-->

                <!-- stats + charts -->
                <!--  <div class="row">
                    <div class="col-12">
                        <div>
                            <a href="<?php /*echo $upgrade_url */
				?>">
                                <img src="<?php /*echo KMCF7MS_IMAGES_URL . '/black_friday.png' */
				?>" alt=""
                                     class="img-fluid" style="border: solid 1px"/>
                            </a>
                            <div class="alert  bg-dark text-white mt-2 d-none">
                                <strong>BLACK FRIDAY PROMO !!!</strong> Use coupon code <strong style="font-size: 30px">FSBFCM2023</strong>
                                from the 24th - 27th of November to get 30% off on all premium plans
                            </div>
                        </div>
                    </div>
                </div>-->
                <div class="row">
                    <div class="col-xl-3">
                        <a href="<?php echo $messages_url ?>" class="card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <span class="text-muted text-uppercase fs-12 fw-bold"><?php esc_html_e( "Total Messages Blocked", KMCF7MS_TEXT_DOMAIN ) ?></span>
                                        <h3 class="mb-0">
											<?php echo get_option( 'kmcfmf_messages_blocked' ); ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3">
                        <a href="<?php echo $messages_url ?>" class="card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <span class="text-muted text-uppercase fs-12 fw-bold"><?php esc_html_e( "Messages Blocked Today", KMCF7MS_TEXT_DOMAIN ) ?></span>
                                        <h3 class="mb-0">
											<?php echo get_option( 'kmcfmf_messages_blocked_today' ); ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3">
                        <a href="<?php echo $settings_url ?>" class="card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <span class="text-muted text-uppercase fs-12 fw-bold"><?php esc_html_e( "Spam Words & Emails", KMCF7MS_TEXT_DOMAIN ) ?></span>
                                        <h3 class="mb-0">
											<?php
											$words  = get_option( 'kmcfmf_restricted_words', '' );
											$emails = get_option( 'kmcfmf_restricted_emails', '' );
											echo( sizeof( explode( ',', $words ) ) + sizeof( explode( ',', $emails ) ) );
											?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3">
						<?php
						if ( ( kmcf7ms_fs()->can_use_premium_code() && ! kmcf7ms_fs()->is_premium() ) ) {
							$link = $upgrade_guide_url;
						} else {
							$link = $upgrade_url;
						}
						if ( kmcf7ms_fs()->is_premium() ) {
							$link = '#';
						}
						?>
                        <a href="<?php echo $link ?>"
                           class="card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <span class="text-muted text-uppercase fs-12 fw-bold"><?php esc_html_e( "Plan", KMCF7MS_TEXT_DOMAIN ) ?></span>
                                        <h3 class="mb-0">
											<?php if ( kmcf7ms_fs()->is_premium() ): ?>
                                                PRO
											<?php else: ?>
                                                FREE
											<?php endif; ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>


                </div>
                <!-- stats + charts -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body pb-0">
                                <h5 class="card-title mb-0 header-title"><?php esc_html_e( "Statistics", KMCF7MS_TEXT_DOMAIN ) ?></h5>
                                <div class="mt-2">
                                    Showing <select name="stats" id="stats">
                                        <option value="7d">7 days</option>
                                        <option value="30d">30 days</option>
                                        <option value="1y">1 year</option>
                                    </select> statistics
                                </div>
                                <div class="text-center alert alert-info mt-2" id="loading_stats">
                                    Loading Statistics...
                                </div>
                                <div class="alert alert-danger text-center mt-2" id="stats_error" style="display:none">
                                    An error occurred. Please try again
                                    <button class="btn btn-sm btn-primary" id="stats_try_again">Try again</button>
                                </div>

                                <div id="stats-chart" class="apex-charts mt-3" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body pb-0">
                                <h5 class="card-title mb-0 header-title"><?php esc_html_e( "Word Frequency", KMCF7MS_TEXT_DOMAIN ) ?></h5>

                                <div id="words-chart" class="apex-charts mt-3" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-body pb-0">
                                <h5 class="card-title mb-0 header-title"><?php esc_html_e( "Email Frequency", KMCF7MS_TEXT_DOMAIN ) ?></h5>

                                <div id="emails-chart" class="apex-charts mt-3" dir="ltr"></div>
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
                        <!--<a href="https://ko-fi.com/kofimokome"
                           class="btn btn-primary" target="_blank"
                           rel="noopener noreferrer"><?php /*esc_html_e( "Buy me Coffee", KMCF7MS_TEXT_DOMAIN ) */
						?></a>-->

                    </div>
                    <h5> Here are a few changes in this version:</h5>
                    <ol>
                        <!--                        <li>Pro users can now receive suggested spam words and emails every month.</li>-->
                        <li>Fix Security loophole</li>
                        <li>Fix Ukrainian anthem playing on Russian sites</li>
                        <li>Fix filters with apostrophes not working as expected</li>
                    </ol>
					<?php /*esc_html_e( "Please help translate this plugin to your language", KMCF7MS_TEXT_DOMAIN ) */
					?><!-- <a
                            href="https://translate.wordpress.org/projects/wp-plugins/cf7-message-filter/"
                            target="_blank"
                            class="btn btn-sm btn-primary"><?php /*esc_html_e( "Translate Now", KMCF7MS_TEXT_DOMAIN ) */
					?></a>-->
                    <!--                    <br>-->
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

    <!-- <div class="row">
		 <form action="https://ko-fi.com/kofimokome" method="post" target="_blank">
			 <input type="hidden" name="hosted_button_id" value="B3JAV39H95RFG"/>
			 <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0"
					name="submit"
					title="Ko-fi is the easiest way for you to start making an income directly from your fans"
					alt="Donate with PayPal button"/>
			 <img alt="" border="0" src="https://www.paypal.com/en_CM/i/scr/pixel.gif" width="1" height="1"/>
		 </form>
	 </div>-->

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->


</div>
<?php $word_options = json_encode( array_keys( $frequent_words ) );
$words              = "[";
foreach ( $frequent_words as $word ) {
	$words .= $word . ",";
}
$words .= "]";

$email_options = json_encode( array_keys( $frequent_emails ) );
$emails        = "[";
foreach ( $frequent_emails as $email ) {
	$emails .= $email . ",";
}
$emails   .= "]";
$ajax_url = admin_url( "admin-ajax.php" );

?>
<script>
    jQuery(document).ready(function ($) {
        let chart;
        const loading_stats = $("#loading_stats");
        const error_container = $("#stats_error");
        const try_again = $("#stats_try_again");
        let mode = '7d';

        function getStats() {
            let formData = new FormData();
            formData.append("action", 'kmcf7_get_stats');
            formData.append("mode", mode);

            updateChat({x_axis: [], y_axis: []});

            loading_stats.show();
            error_container.hide()

            fetch("<?php echo $ajax_url?>", {
                method: 'POST',
                body: formData
            })
                .then(async response => {
                    if (!response.ok) {
                        const e = await response.text();
                        let message = "Something went wrong";
                        try {
                            const response_json = JSON.parse(e)
                            if (response_json.data)
                                message = response_json.data.message ?? response_json.data.toString()
                        } catch (e) {
                            // Silence is golden
                        }
                        throw new Error(message)
                    } else
                        return response.json()
                }).then((response) => updateChat(response.data))
                .catch(error => {
                    error_container.show()
                    loading_stats.hide()
                })
        }

        $("#stats").change((e) => {
            mode = e.target.value;
            getStats()
        })

        function updateChat(data) {
            loading_stats.hide();
            error_container.hide()
            // start of transaction charts
            chart.updateOptions({
                xaxis: {
                    type: 'text',
                    categories: data.x_axis,
                },
                series: [{
                    name: 'Messages blocked',
                    data: data.y_axis,
                }],
            })

        }

        try_again.click(() => getStats())
        const options = {
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
                categories: [],
            },
            series: [{
                name: 'Messages blocked',
                data: [],
            }],
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

        chart = new ApexCharts(document.querySelector("#stats-chart"), options);
        chart.render();
        const word_options = {
            series: <?php echo $words?>,
            chart: {
                // width: 380,
                type: 'pie',
                height: '500px'
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

        const word_chart = new ApexCharts(document.querySelector("#words-chart"), word_options);
        word_chart.render();
        const email_options = {
            series: <?php echo $emails?>,
            chart: {
                height: '500px',
                type: 'pie',
            },
            labels: <?php echo $email_options?>,
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

        const email_chart = new ApexCharts(document.querySelector("#emails-chart"), email_options);
        email_chart.render();
        getStats('7d');

		<?php if(get_option( 'kmcfmf_version', '0' ) != ( KMCFMessageFilter::getInstance() )->getVersion()):?>
        $('#myModal').modal()
		<?php update_option( 'kmcfmf_version', ( KMCFMessageFilter::getInstance() )->getVersion() );endif;?>

    });
</script>
<!-- END wrapper -->