$(function() {
	var site_url = "http://emg.1yop.com/";

	ncovStatisticsDayTotal();
	ncovArticleList(1, getNcovArticleList_news);
	ncovArticleList(2, getNcovArticleList_rumorRefuting);
	nconvstatisticsTotal(146, getCnonvstatisticsTotal_hk);
	nconvstatisticsTotal(10, getCnonvstatisticsTotal_all);
	ncovFchlist();
	ncovStatisticsCityTotal();

	function ajax(url, obj, cb) {
		$.post({
			url: url,
			data: obj
		}).done(function(ret) {
			cb(ret)
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

	/*tab栏点击*/
	$('.tab').on('click', 'li', function() {
		if (!$(this).hasClass('active')) {


			//计算目标高度
			var url = $(this).data('url');
			var dom = $('.' + url);
			var dom_h = dom.offset().top;
			//移动高度
			$(window).scrollTop(dom_h - 60 - $('.tab').height());
			tabChange($(this).index());
		}
	})

	/*tab栏切换效果*/
	function tabChange(i) {
		$('.tab li').removeClass('active');
		$('.tab li').eq(i).addClass('active');
	}

	/*监听滚动条*/
	var p = 0;
	var t = 0;
	$(window).scroll(function(e) {
		//tab想上高度
		var tab = $('.tab-box');
		var tab_h = tab.offset().top;
		//window滚动条高度
		var w_h = $(this).scrollTop();
		/*滚动条超过了tab就加上active*/
		if (w_h >= tab_h) {
			tab.find('.tab').addClass('active');
		} else {
			tab.find('.tab').removeClass('active');
		}

		//计算其他的向上高度

		//地图
		var map_h = $('.map-box').offset().top;
		var mapHeight = $('.map-box').height();
		//新闻
		var news_h = $('.news-box').offset().top;
		var newsHeight = $('.news-box').height();
		//辟谣
		var rumorRefuting_h = $('.rumorRefuting-box').offset().top;
		var rumorRefutingHeight = $('.rumorRefuting-box').height();
		//生活
		var life_h = $('.life-box').offset().top;
		var lifeHeight = $('.life-box').height();

		//tab自身高度
		var tabHeight = tab.height() * 2;

		//判断滚动高度  计算范围  
		if (map_h - tabHeight - 60 <= w_h && map_h + mapHeight / 2 - tabHeight >= w_h) {
			tabChange(0)
		} else if (news_h - tabHeight - 60 <= w_h && news_h + newsHeight / 2 - tabHeight >= w_h) {
			tabChange(1)
		} else if (rumorRefuting_h - tabHeight - 60 <= w_h && rumorRefuting_h + rumorRefutingHeight / 2 - tabHeight >= w_h) {
			tabChange(2)

		} else if (life_h <= w_h) {
			tabChange(3)
		}

		if ($('.app').height() - window.innerHeight - w_h <= 0) {
			tabChange(3)
		}

	})

	/*疫情曲线图统计*/
	function ncovStatisticsDayTotal() {
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

	// openEcharts($('#container')[0],['02-01','02-02','02-03','02-04','02-05','02-06','02-07'],[1,2,3,4,5,6,7],[11,12,13,14,15,16,17],[21,22,23,24,25,26,27])
	//折线图
	function openEcharts(dom, days, defines, deaths, cures) {

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
					// saveAsImage: {}
				}
			},
			xAxis: {
				type: 'category',
				boundaryGap: false,
				data: days,
				//             axisLabel: {
				//             	rotate: 30, // 旋转角度
				//         		interval: 0  //设置X轴数据间隔几个显示一个，为0表示都显示
				// }
			},
			yAxis: {
				type: 'value',
				// min:0, // 设置y轴刻度的最小值
				// max:60,  // 设置y轴刻度的最大值
				// splitNumber:3,  // 设置y轴刻度间隔个数
			},
			series: [{
				name: '确诊',
				type: 'line',
				stack: '确诊',
				data: defines
			}, {
				name: '死亡',
				type: 'line',
				stack: '死亡',
				data: deaths
			}, {
				name: '治愈',
				type: 'line',
				stack: '治愈',
				data: cures
			}, ]
		};

		echart.setOption(option);
	}



	/*疫情新闻消息*/
	function ncovArticleList(class_id, cb) {
		var data = {};
		data = {
			page: 1,
			limit: 10,
			class_id: class_id
		}
		ajax(site_url + 'api/v1/ncov/article/list', data, cb);
	}
	/*疫情新闻消息回调*/
	function getNcovArticleList_news(data) {
		// console.log('疫情新闻消息回调'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			for (var i = 0; i < data.data.length; i++) {
				appList(data.data[i], $('.news-list'), true);
			}
		}
	}

	/*疫情辟谣消息回调*/
	function getNcovArticleList_rumorRefuting(data) {
		// console.log('疫情辟谣消息回调'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			for (var i = 0; i < data.data.length; i++) {
				appList(data.data[i], $('.rumorRefuting-list'), false);
			}
		}
	}


	/*生成新闻列表和辟谣列表*/
	function appList(data, par, flag) {
		var str = $('<li class="list">\
						<a href="' + data.url + '">\
							<div class="list-title">\
								<span class="list-tips">谣言</span>' + data.title + '</div>\
							<div class="list-time-box">\
								<p class="list-time">时间：' + data.created_at + '</p>\
								<p class="list-source">来源：' + data.source + '</p>\
							</div>\
						</a>\
					</li>');
		if (flag) {
			str.addClass('news');
		}
		// str.data('url',data.url);
		par.append(str);
	}

	/*获取疫情统计*/
	function nconvstatisticsTotal(id, cb) {
		data = {
			province_id: id
		}
		ajax(site_url + 'api/v1/ncov/statistics/total', data, cb);
	}
	/*获取疫情统计回调(海南)*/
	function getCnonvstatisticsTotal_hk(data) {
		// console.log('获取疫情统计回调(海南)'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			$('.count-list-box').eq(0).find('.num').eq(0).html(data.msg.totals.define);
			$('.count-list-box').eq(0).find('.num').eq(1).html(data.msg.totals.criticall);
			$('.count-list-box').eq(0).find('.num').eq(2).html(data.msg.totals.cure);
			$('.count-list-box').eq(0).find('.num').eq(3).html(data.msg.totals.death);
			$('.count-time').html('截至 ' + data.msg.at);

		}
	}

	/*获取疫情统计回调(全国)*/
	function getCnonvstatisticsTotal_all(data) {
		// console.log('获取疫情统计回调(全国)'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			$('.count-list-box').eq(1).find('.num').eq(0).html(data.msg.totals.define);
			$('.count-list-box').eq(1).find('.num').eq(1).html(data.msg.totals.criticall);
			$('.count-list-box').eq(1).find('.num').eq(2).html(data.msg.totals.cure);
			$('.count-list-box').eq(1).find('.num').eq(3).html(data.msg.totals.death);
		}
	}

	/*疫情衣食住行*/
	function ncovFchlist() {
		data = {
			page: 1,
			limit: 20,
		}
		ajax(site_url + 'api/v1/ncov/fch/list', data, getNcovFchlist);
	}
	/*疫情衣食住行回调*/
	function getNcovFchlist(data) {
		// console.log('疫情衣食住行回调'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			for (var i = 0; i < data.data.length; i++) {
				for (var i = 0; i < data.data.length; i++) {
					appendLife(data.data[i]);
				}
			}
		}
	}
	/*生成衣食住行列表*/
	function appendLife(data) {
		var str = $('<tr>\
						<td>' + data.class_name + '</td>\
						<td colspan="3">' + data.title + '</td>\
						<td><span class="state"></span></td>\
						<td colspan="2">' + data.usiness_hours + '</td>\
						<td><a href="' + data.url + '">查看</a></td>\
					</tr>');
		if (data.business_status == 1) {
			str.find('.state').html('正常');
			str.find('.state').addClass('yes');
		} else if (data.business_status == 2) {
			str.find('.state').html('歇业');
			str.find('.state').addClass('no');
		} else if (data.business_status == 3) {
			str.find('.state').html('停运');
			str.find('.state').addClass('no');
		}

		$('.life-table').append(str);
	}

	/*疫情城市数据列表*/
	function ncovStatisticsCityTotal() {
		data = {
			page: 1,
			limit: 20,
		}
		ajax(site_url + 'api/v1/ncov/statistics/city/total', data, getNcovStatisticsCityTotal);
	}
	/*疫情城市数据列表回调*/
	function getNcovStatisticsCityTotal(data) {
		// console.log('疫情城市数据列表回调'+JSON.stringify(judgeCode(data)));
		if (judgeCode(data)) {
			for (var i = 0; i < data.data.length; i++) {
				for (var i = 0; i < data.data.length; i++) {
					appendCity(data.data[i]);
				}
			}
		}
	}

	function appendCity(data) {
		var str = $('<tr>\
						<td class="name">' + data.name + '</td>\
						<td class="define">' + data.define + '</td>\
						<td class="death">' + data.death + '</td>\
						<td class="cure">' + data.cure + '</td>\
					</tr>');
		if (data.define == 0) {
			str.find('.define').addClass('zero');
		}
		if (data.death == 0) {
			str.find('.death').addClass('zero');
		}
		if (data.cure == 0) {
			str.find('.cure').addClass('zero');
		}
		$('.city-table').append(str);
	}
})