$(function() {
	var site_url = "http://emg.1yop.com/";

	nconvstatisticsTotal();
	ncovtrackList();
	ncovplaceTotal();
	// ncovStatisticsDayTotal();

	function ajax(url, obj, cb) {
		$.post({
			url: url,
			data: obj
		}).done(function(ret) {
			cb(ret);
		}).fail(function(err) {
			console.log(err)
		});
	}

	function judgeCode(data) {
		if (data.code == 500) {
			console.log('data.code500+:' + data.code)
			console.log('data.code500+++++:' + data)
			return false;
		} else if (data.code == 401) {
			console.log('data.code401+:' + data.code)
			console.log('data.code401+++++:' + data)
			return false;
		} else {
			return data;
		}
	}

	/*点击城市名字*/
	$('.area-box').on('click', '.area-name-box', function() {
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			/*关闭街道选择*/
			$(this).next().removeClass('active');
			$('.area-road-box').removeClass('active');
		} else {
			/*关闭其他的城市街道*/
			$('.area-name-box').removeClass('active');
			$('.area-choice-box').removeClass('active');
			$('.area-road-box').removeClass('active');
			$('.area-road').removeClass('active');
			$(this).addClass('active');
			/*开启街道选择*/
			var road = $(this).next();
			var h = '全部';
			var c = road.children().eq(0).html();
			// console.log(c.indexOf(h))
			if (c.indexOf(h) != -1) {
				road.removeClass('active');
			} else {
				road.addClass('active');
			}

			roadShow(road);
		}
	});

	function roadShow(obj) {
		$.each(obj.children(), function(i, v) {
			if ($(v).hasClass('active')) {
				var obj_id = $(v).data('id');
				var par = $(v).closest('.area-list');
				$.each(par.find('.area-road-box'), function(j, k) {
					var son_id = $(k).data('id');
					if (son_id == obj_id) {
						$(k).addClass('active');
					}
				})
			}
		})
	}

	/*点击区域名*/
	$('.area-box').on('click', '.area-choice', function() {
		if ($(this).hasClass('active')) {
			// $(this).removeClass('active');
		} else {
			$('.area-choice').closest('.area-choice-box.active').children().removeClass('active');
			$(this).addClass('active');
			$('.area-road-box').removeClass('active');
			$('.area-road').removeClass('active');
			roadShow($(this).closest('.area-choice-box'));
		}
	})

	/*点击街道名*/
	$('.area-box').on('click', '.area-road-name', function() {
		if ($(this).closest('.area-road').hasClass('active')) {
			$(this).closest('.area-road').removeClass('active');
		} else {
			$('.area-road').removeClass('active');
			$(this).closest('.area-road').addClass('active');
		}
	})

	/*查询同乘*/
	$('.query-btn').click(function() {
		api.setPrefs({ //广告图片
			key: 'flash_web_url',
			value: 'https://2019ncov.133.cn/virus-trip/Vue/virusTrip/home?ptid=dxys'
		});
		api.openWin({
			name: 'openWeb',
			url: '../openWeb.html',
			slidBackEnabled: false,
			reload: true,
			pageParam: {
				name: ''
			}
		});
	})

	/*文字现实隐藏*/
	$('.open-btn').click(function() {

		if ($('.epidemic-rex-box').hasClass('hide')) {
			$('.epidemic-rex-box').removeClass('hide');
		} else {
			$('.epidemic-rex-box').addClass('hide');
		}
	})

	/*获取疫情统计*/
	function nconvstatisticsTotal() {
		var data = {};
		data.province_id = 146;
		ajax(site_url + 'api/v1/ncov/statistics/total', data, getCnonvstatisticsTotal);
	}
	/*获取疫情统计回调*/
	function getCnonvstatisticsTotal(data) {
		// console.log('获取疫情统计回调'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			$('.compare_totals_define').html('+' + data.msg.compare_totals.define);
			$('.compare_totals_doubt').html('+' + data.msg.compare_totals.doubt);
			$('.compare_totals_criticall').html('+' + data.msg.compare_totals.criticall);
			$('.compare_totals_death').html('+' + data.msg.compare_totals.death);
			$('.compare_totals_cure').html('+' + data.msg.compare_totals.cure);

			$('.totals_define').html('+' + data.msg.totals.define);
			$('.totals_doubt').html('+' + data.msg.totals.doubt);
			$('.totals_criticall').html('+' + data.msg.totals.criticall);
			$('.totals_death').html('+' + data.msg.totals.death);
			$('.totals_cure').html('+' + data.msg.totals.cure);

			$('.count-time').html('截至 ' + data.msg.at);

		}
	}

	/*疫情城市列表*/
	function ncovtrackList() {
		var data = {};
		data.province_id = 146;
		ajax(site_url + 'api/v1/ncov/track/list', data, getNcovtrackList);
	}
	/*疫情城市列表回调*/
	function getNcovtrackList(data) {
		// console.log('疫情城市列表回调'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			for (var i = 0; i < data.msg.length; i++) {
				appendCity(data.msg[i])
			}
		}
	}

	/*疫情交通统计*/
	function ncovplaceTotal() {
		var data = {};
		data.province_id = 146;
		ajax(site_url + 'api/v1/ncov/place/total', data, getNcovplaceTotal);
	}

	/*疫情交通统计回调*/
	function getNcovplaceTotal(data) {
		// console.log('疫情交通统计回调'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			$('.area-name').html('截止：' + data.msg.at + '时，新增<span class="orange"> ' + data.msg.position_count + ' </span>处位置,<span class="orange"> ' + data.msg.traffic_count + ' </span>个交通信息')
		}
	}

	/*生成城市列表*/
	function appendCity(data) {
		var lis = $('<li class="area-list"></li>');
		$('.area-list-box').append(lis);

		/*生成名字和数量*/
		var city = $('<div class="area-name-box">\
	                        <div class="line"></div>\
	                        <p class="area-list-name">' + data.city_name + '</p>\
	                        <div class="ico">\
	                            <img src="/home/image/ncov/news-down.png" alt="">\
	                        </div>\
	                        <p class="area-num"></p>\
	                    </div>');
		lis.append(city);
		if (data.add == 0) {
			city.find('.area-num').html(' 共 ' + data.sum + ' 处');
		} else {
			city.find('.area-num').html('<span class="orange">+' + data.add + '</span> 共 ' + data.sum + ' 处');
		}
		/*生成区域列表*/
		appendArea(lis, data.area_arr);

	}

	/*生成区域列表*/
	function appendArea(par, data) {
		var area_box = $('<ul class="area-choice-box"></ul>');
		par.append(area_box);
		$.each(data, function(i, v) {
			// console.log(v);
			var area = $('<li class="area-choice">' + v.area_name + '(' + v.total + ')</li>');
			area.data('id', v.area_id);
			if (i == 0) {
				area.addClass('active');
			}
			area_box.append(area);
			/*生成街道列表*/
			appendRoad(par, v)
		})
	}

	/*生成街道列表*/
	function appendRoad(par, data) {
		var road_box = $('<ul class="area-road-box"></ul>');
		road_box.data('id', data.area_id);
		par.append(road_box);
		$.each(data.road_arr, function(i, v) {
			// console.log(v)
			var road = $('<li class="area-road">\
	                        <p class="area-road-name">' + v.road_name + '<span>' + (v.isAdd ? 'new' : '') + '</span></p>\
	                      </li>');
			road.data('id', v.road_id);
			road_box.append(road);
			/*生成患者列表*/
			appendPatient(road, v.patient_arr, v.road_name);
		})
	}

	/*生成患者列表*/
	function appendPatient(par, data, name) {
		var patient_box = $('<ul class="area-content-list"></ul>');
		par.append(patient_box);
		$.each(data, function(i, v) {
			// console.log(v)
			var str = v.patient_content;
			patient_content = str.replace(/<[^>]+>/g, "");
			var reg = new RegExp(name, "g")
			content = patient_content.replace(reg, "<em>" + name + "</em>");
			// console.log(content)
			var patient = $('<li class="area-content">\
	                            <div class="content"><span class="num">' + v.patient_id + '号确诊</span>' + content + '</div>\
	                            <p class="source">来源：' + v.patient_source + '</p>\
	                        </li>');
			patient_box.append(patient);
		})
	}

	/*疫情曲线图统计*/
	function ncovStatisticsDayTotal() {
		var data = {};
		data = {
			province_id: 146
		}
		ajax(site_url + 'api/v1/ncov/statistics/day/total', data, getNcovStatisticsDayTotal);
	}

	/*疫情曲线图统计回调*/
	function getNcovStatisticsDayTotal(data) {
		// console.log('疫情曲线图统计回调'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			openEcharts($('#container')[0], data.data.days, data.data.defines, data.data.deaths, data.data.cures);
		}
	}

	function openEcharts(dom, days, defines, deaths, cures) {
		// console.log(echarts)
		var echart = echarts.init(dom);
		option = {
			title: {
				// text: '折线图堆叠'
			},
			tooltip: {
				trigger: 'axis'
			},
			legend: {
				data: ['确诊', '死亡', '治愈']
			},
			grid: {
				left: '0',
				right: '4%',
				bottom: '3%',
				containLabel: true
			},
			toolbox: {
				feature: {
					saveAsImage: {}
				}
			},
			xAxis: {
				type: 'category',
				boundaryGap: false,
				data: days
			},
			yAxis: {
				type: 'value'
			},
			series: [{
				name: '确诊',
				type: 'line',
				stack: '总量',
				data: defines
			}, {
				name: '死亡',
				type: 'line',
				stack: '总量',
				data: deaths
			}, {
				name: '治愈',
				type: 'line',
				stack: '总量',
				data: cures
			}, ]
		};

		echart.setOption(option);
	}

	/*搜索*/
	var val = null;
	var page = 1;
	$('.search-btn').click(function() {
		val = $('.search-inp').val();

		if (val.replace(/\s/g, '') == '' || val.replace(/\s/g, '') == ' ') {
			$('.search-inp').val('');
			alert('查询不能为空');
		} else {
			page = 1;
			$('.search-result-box').empty();
			ncovTrackSerach();
		}
	})

	/*查询*/
	function ncovTrackSerach() {
		// console.log(config.api.ncovTrackSerach)
		var data = {};
		data = {
			province_id: 146,
			page: page,
			limit: 10,
			search: val
		}
		// console.log(JSON.stringify(data))
		ajax(site_url + 'api/v1/ncov/track/serach', data, getScovTrackSerach);
	}
	/*查询回调*/
	function getScovTrackSerach(data) {
		// console.log('查询回调'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			$('.search-tips-box>p').html(val + '<span> ' + data.count + ' </span>个相关记录');
			if (data.data.length != 0) {
				for (var i = 0; i < data.data.length; i++) {
					appendSearchList(data.data[i])
				}
				$('.search-tips-box').addClass('active');
				$('.more-btn').show();
			} else {
				$('.more-btn').hide();
			}

		}
	}

	/*查询结果列表渲染*/
	function appendSearchList(data) {
		var str = data.content;
		search_content = str.replace(/<[^>]+>/g, "");
		var reg = new RegExp(val.toLocaleUpperCase(), "g")
		content = search_content.replace(reg, "<em>" + val.toLocaleUpperCase() + "</em>");
		search_list = $('<li class="search-result-list">\
	                        <div class="search-result-content">\
	                            <span class="num">' + data.title + '</span>' + content + '</em>\
	                        </div>\
	                        <div class="search-result-source">来源：' + data.source + '</div>\
	                    </li>');
		$('.search-result-box').append(search_list);
	}

	/*清空搜索*/
	$('.search-tips-ret').click(function() {
		$('.search-result-box').empty();
		$('.search-tips-box').removeClass('active');
		$('.search-inp').val('');
		$('.more-btn').hide();
	})

	/*点击查看更多*/
	$('.more-btn').click(function() {
		page++;
		ncovTrackSerach();
	})
})