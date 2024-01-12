				<div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="box-title">Estadísticas de Llamadas</h4>
                            </div>
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="card-body">
                                        <!-- <canvas id="TrafficChart"></canvas>   -->
                                        <div id="traffic-chart" style="min-height: 335px;" class="traffic-chart">
											<svg width="100%" height="100%" class="ct-chart-line" style="width: 100%; height: 100%;">
												<g class="ct-grids">
													<line x1="50" x2="50" y1="15" y2="300" class="ct-grid ct-horizontal"></line>
													<line x1="192.73125" x2="192.73125" y1="15" y2="300" class="ct-grid ct-horizontal"></line>
													<line x1="335.4625" x2="335.4625" y1="15" y2="300" class="ct-grid ct-horizontal"></line>
													<line x1="478.19374999999997" x2="478.19374999999997" y1="15" y2="300" class="ct-grid ct-horizontal"></line>
													<line x1="620.925" x2="620.925" y1="15" y2="300" class="ct-grid ct-horizontal"></line>
													<line x1="763.65625" x2="763.65625" y1="15" y2="300" class="ct-grid ct-horizontal"></line>
													<line y1="300" y2="300" x1="50" x2="763.65625" class="ct-grid ct-vertical"></line>
													<line y1="259.2857142857143" y2="259.2857142857143" x1="50" x2="763.65625" class="ct-grid ct-vertical"></line>
													<line y1="218.57142857142856" y2="218.57142857142856" x1="50" x2="763.65625" class="ct-grid ct-vertical"></line>
													<line y1="177.85714285714286" y2="177.85714285714286" x1="50" x2="763.65625" class="ct-grid ct-vertical"></line>
													<line y1="137.14285714285714" y2="137.14285714285714" x1="50" x2="763.65625" class="ct-grid ct-vertical"></line>
													<line y1="96.42857142857142" y2="96.42857142857142" x1="50" x2="763.65625" class="ct-grid ct-vertical"></line>
													<line y1="55.71428571428572" y2="55.71428571428572" x1="50" x2="763.65625" class="ct-grid ct-vertical"></line>
													<line y1="15" y2="15" x1="50" x2="763.65625" class="ct-grid ct-vertical"></line>
												</g>
												<g>
													<g class="ct-series ct-series-a"><path d="M50,300L50,300C97.577,251.143,145.154,200.89,192.731,153.429C240.308,105.967,287.885,15,335.463,15C383.04,15,430.617,83.901,478.194,96.429C525.771,108.956,573.348,106.526,620.925,120.857C668.502,135.189,716.079,240.286,763.656,300L763.656,300Z" class="ct-area"></path></g>
													<g class="ct-series ct-series-b"><path d="M50,300L50,300C97.577,210.429,145.154,31.286,192.731,31.286C240.308,31.286,287.885,177.857,335.463,177.857C383.04,177.857,430.617,137.143,478.194,137.143C525.771,137.143,573.348,157.603,620.925,177.857C668.502,198.111,716.079,257.657,763.656,297.557L763.656,300Z" class="ct-area"></path></g>
													<g class="ct-series ct-series-c"><path d="M50,300L50,300C97.577,259.286,145.154,215.663,192.731,177.857C240.308,140.051,287.885,72,335.463,72C383.04,72,430.617,177.857,478.194,177.857C525.771,177.857,573.348,55.714,620.925,55.714C668.502,55.714,716.079,191.429,763.656,259.286L763.656,300Z" class="ct-area"></path></g>
												</g>
												<g class="ct-labels">
													<foreignObject style="overflow: visible;" x="50" y="305" width="142.73125" height="20"><span class="ct-label ct-horizontal ct-end" style="width: 143px; height: 20px;">Ene</span></foreignObject>
													<foreignObject style="overflow: visible;" x="192.73125" y="305" width="142.73125" height="20"><span class="ct-label ct-horizontal ct-end" style="width: 143px; height: 20px;">Feb</span></foreignObject>
													<foreignObject style="overflow: visible;" x="335.4625" y="305" width="142.73125" height="20"><span class="ct-label ct-horizontal ct-end" style="width: 143px; height: 20px;">Mar</span></foreignObject>
													<foreignObject style="overflow: visible;" x="478.19374999999997" y="305" width="142.73125" height="20"><span class="ct-label ct-horizontal ct-end" style="width: 143px; height: 20px;">Abr</span></foreignObject>
													<foreignObject style="overflow: visible;" x="620.925" y="305" width="142.73125000000005" height="20"><span class="ct-label ct-horizontal ct-end" style="width: 143px; height: 20px;">May</span></foreignObject>
													<foreignObject style="overflow: visible;" x="763.65625" y="305" width="30" height="20"><span class="ct-label ct-horizontal ct-end" style="width: 30px; height: 20px;">Jun</span></foreignObject>
													<foreignObject style="overflow: visible;" y="259.2857142857143" x="10" height="40.714285714285715" width="30"><span class="ct-label ct-vertical ct-start" style="height: 41px; width: 30px;">0</span></foreignObject>
													<foreignObject style="overflow: visible;" y="218.57142857142856" x="10" height="40.714285714285715" width="30"><span class="ct-label ct-vertical ct-start" style="height: 41px; width: 30px;">5000</span></foreignObject>
													<foreignObject style="overflow: visible;" y="177.85714285714283" x="10" height="40.71428571428571" width="30"><span class="ct-label ct-vertical ct-start" style="height: 41px; width: 30px;">10000</span></foreignObject>
													<foreignObject style="overflow: visible;" y="137.14285714285714" x="10" height="40.71428571428572" width="30"><span class="ct-label ct-vertical ct-start" style="height: 41px; width: 30px;">15000</span></foreignObject>
													<foreignObject style="overflow: visible;" y="96.42857142857142" x="10" height="40.71428571428572" width="30"><span class="ct-label ct-vertical ct-start" style="height: 41px; width: 30px;">20000</span></foreignObject>
													<foreignObject style="overflow: visible;" y="55.71428571428572" x="10" height="40.714285714285694" width="30"><span class="ct-label ct-vertical ct-start" style="height: 41px; width: 30px;">25000</span></foreignObject>
													<foreignObject style="overflow: visible;" y="15" x="10" height="40.71428571428572" width="30"><span class="ct-label ct-vertical ct-start" style="height: 41px; width: 30px;">30000</span></foreignObject>
													<foreignObject style="overflow: visible;" y="-15" x="10" height="30" width="30"><span class="ct-label ct-vertical ct-start" style="height: 30px; width: 30px;">35000</span></foreignObject>
												</g>
											</svg>
										</div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card-body">
                                        <div class="progress-box progress-1">
                                            <h4 class="por-title">Abandonadas</h4>
                                            <div class="por-txt">96,930 (40%)</div>
                                            <div class="progress mb-2" style="height: 5px;">
                                                <div class="progress-bar bg-flat-color-1" role="progressbar" style="width: 40%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="progress-box progress-2">
                                            <h4 class="por-title">Sabotajes</h4>
                                            <div class="por-txt">3,220 (24%)</div>
                                            <div class="progress mb-2" style="height: 5px;">
                                                <div class="progress-bar bg-flat-color-2" role="progressbar" style="width: 24%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="progress-box progress-2">
                                            <h4 class="por-title">Informativas</h4>
                                            <div class="por-txt">29,658 (60%)</div>
                                            <div class="progress mb-2" style="height: 5px;">
                                                <div class="progress-bar bg-flat-color-3" role="progressbar" style="width: 60%;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="progress-box progress-2">
                                            <h4 class="por-title">Emergencias efectivas</h4>
                                            <div class="por-txt">99,658 (90%)</div>
                                            <div class="progress mb-2" style="height: 5px;">
                                                <div class="progress-bar bg-flat-color-4" role="progressbar" style="width: 90%;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div> 
									<!-- /.card-body -->
                                </div>
                            </div> 
							<!-- /.row -->
                        </div>
                    </div><!-- /# column -->
                </div>