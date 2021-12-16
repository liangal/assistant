<?php
namespace app\services\manage;

use app\repository\UsersRepository;
use app\repository\VersionRepository;
use app\repository\SiteStatisticsRepository;
use app\repository\OrderRepository;

class MainServices
{


    protected $orderRepository;
    protected $usersRepository;

    public function __construct(OrderRepository $orderRepository,UsersRepository $usersRepository){

        $this->orderRepository = $orderRepository;
        $this->usersRepository = $usersRepository;
    }

    /**
     * 统计数据
     *
     * @return array
     */
    public function getData()
    {

        $wait_send = $this->orderWaitSend();
        $wait_refund = $this->orderWaitRefund();
        $order_finish_total = $this->orderFinishTotal('today');
        $order_finish_money = $this->orderFinishMoney('today');

        //订单图表数据
        $chart = $this->chartsTotal();
        //card对比数据
        $ratio_date = $this->dateToArr();

        $data = array(
            'wait_send' => $wait_send,
            'wait_refund' => $wait_refund,
            'order_finish_total' => $order_finish_total,
            'order_finish_money' => $order_finish_money,
            'order_eachday'=> array('days'=>$chart['all_date']['days'],'money'=>$chart['money']['money_eachday'],'order'=>$chart['order']['order_eachday']),
            'order_eachmonth'=> array('days'=>$chart['all_date']['month'],'money'=>$chart['money']['money_eachmonth'],'order'=>$chart['order']['order_eachmonth']),
            'order_eachyear'=> array('days'=>$chart['all_date']['year'],'money'=>$chart['money']['money_eachyear'],'order'=>$chart['order']['order_eachyear']),
            'user_eachday'=> array('days'=>$chart['all_date']['days'],'user'=>$chart['user']['user_eachday']),
            'user_eachmonth'=> array('days'=>$chart['all_date']['month'],'user'=>$chart['user']['user_eachmonth']),
            'user_eachyear'=> array('days'=>$chart['all_date']['year'],'user'=>$chart['user']['user_eachyear']),
            'ratio_date' => $ratio_date

        );

        return $data;
    }
//====================================================================
    /**
     * 待发货订单数
     */
    protected function orderWaitSend()
    {
        return $this->orderRepository->orderTotal(1);
    }

    /**
     * 待退款订单数(退款中)
     */
    protected function orderWaitRefund()
    {
        return $this->orderRepository->orderTotal(6);
    }
//=============指定日期=========================
    /**
     * 今日订单总数（已付款）
     */
    protected function orderFinishTotal($day)
    {
        return $this->orderRepository->dealOrderTotal(8,$day);
    }

    /**
     * 交易额（下单付款）
     */
    protected function orderFinishMoney($day)
    {
        return number_format($this->orderRepository->oderMoney(8,$day),2);
    }

    /**
     * 近30天订单统计图表
     */
    /* protected function orderEachdayTotal()
     {
         $startdate =strtotime("-30 day");
         $enddate = strtotime("today");
         $data = array('date'=>array(),'order_eachday'=>array(),'money_eachday'=>array());
         while ($startdate < $enddate){
             //$data['date'][] =$startdate;
             $data['date'][] =date("m-d",$startdate);
             $data['order_eachday'][] = $this->orderRepository->dealOrderTotal(8,[date("Y-m-d",$startdate),date("Y-m-d",strtotime("tomorrow",$startdate))]);
             $data['money_eachday'][] = $this->orderRepository->oderMoney(8,[date("Y-m-d",$startdate),date("Y-m-d",strtotime("tomorrow",$startdate))]);
             $startdate =strtotime("tomorrow",$startdate);
         }
         return $data;
     }*/

    /**
     * 统计数据
     */
    protected function chartsTotal()
    {
        $startdate = strtotime("-7 day");
        $enddate = strtotime("today");

        $data = array('all_date'=>array('days'=>array(),'month'=>array(),'year'=>array()),
            'order'=>array('order_eachday'=>array(),'order_eachyear'=>array(),'order_eachmonth'=>array()),
            'money'=>array('money_eachday'=>array(),'money_eachyear'=>array(),'money_eachmonth'=>array()),
            'user'=>array('user_eachday'=>array(),'user_eachyear'=>array(),'user_eachmonth'=>array()));

        //统计上周七天至今每天数据
        while ($startdate < $enddate){
            //$data['date'][] =$startdate;
            $data['all_date']['days'][] = date("m-d",$startdate);
            //统计订单
            $data['order']['order_eachday'][] = $this->orderRepository->dealOrderTotal(8,[date("Y-m-d",$startdate),date("Y-m-d",strtotime("tomorrow",$startdate))]);
            $data['money']['money_eachday'][] = $this->orderRepository->oderMoney(8,[date("Y-m-d",$startdate),date("Y-m-d",strtotime("tomorrow",$startdate))]);
            //统计用户
            $data['user']['user_eachday'][] = $this->usersRepository->dayForTotal([date("Y-m-d",$startdate),date("Y-m-d",strtotime("tomorrow",$startdate))]);
            $startdate =strtotime("tomorrow",$startdate);
        }
        //统计上个月今天至今每天数据
        $startdate = strtotime("-1 month -1 days"); //
        while ($startdate < $enddate){
            //$data['date'][] =$startdate;
            $data['all_date']['month'][] = date("m-d",$startdate);
            //统计订单
            $data['order']['order_eachmonth'][] = $this->orderRepository->dealOrderTotal(8,[date("Y-m-d",$startdate),date("Y-m-d",strtotime("tomorrow",$startdate))]);
            $data['money']['money_eachmonth'][] = $this->orderRepository->oderMoney(8,[date("Y-m-d",$startdate),date("Y-m-d",strtotime("tomorrow",$startdate))]);
            //统计用户
            $data['user']['user_eachmonth'][] = $this->usersRepository->dayForTotal([date("Y-m-d",$startdate),date("Y-m-d",strtotime("tomorrow",$startdate))]);
            $startdate =strtotime("tomorrow",$startdate);
        }

        //统计一年内年每月数据
        $startdate = strtotime(date('Y-m-01', strtotime('-1 year'))); //一年前本月第一天
        while (date("Y-m",$startdate) <= date("Y-m",$enddate)){
            $data['all_date']['year'][] = date("Y-m",$startdate);
            //统计订单
            $data['order']['order_eachyear'][] = $this->orderRepository->dealOrderTotal(8,[$startdate,date("Y-m-d",strtotime("+1 month -1 day",$startdate))]);
            $data['money']['money_eachyear'][] = $this->orderRepository->oderMoney(8,[$startdate,date("Y-m-d",strtotime("+1 month -1 day",$startdate))]);
            //统计用户
            $data['user']['user_eachyear'][] = $this->usersRepository->dayForTotal([$startdate,date("Y-m-d",strtotime("+1 month -1 day",$startdate))]);
            $startdate =strtotime("+1 month",$startdate);
        }



        return $data;
    }

    /**
     * 对比数据返回
     */
    protected function dateToArr()
    {
        //年月周三组对比数据
        $date = array('week_ratio'=>array('last_sales'=>0,'recent_sales'=>0,'sale_rate'=>0,'last_orders'=>0,'recent_sales'=>0,'order_rate'=>0),
            'month_ratio'=>array('last_sales'=>0,'recent_sales'=>0,'sale_rate'=>0,'last_orders'=>0,'recent_sales'=>0,'order_rate'=>0),
            'year_ratio'=>array('last_sales'=>0,'recent_sales'=>0,'sale_rate'=>0,'last_orders'=>0,'recent_sales'=>0,'order_rate'=>0));
        //本周与上周

        $date['week_ratio']['last_sales'] = $this->orderFinishMoney('last week');
        $date['week_ratio']['recent_sales'] = $this->orderFinishMoney('week');

        $date['week_ratio']['sale_rate'] = (int)$this->calculateRate($date['week_ratio']['recent_sales'],$date['week_ratio']['last_sales']);

        $date['week_ratio']['last_orders'] = $this->orderFinishTotal('last week');
        $date['week_ratio']['recent_orders'] = $this->orderFinishTotal('week');

        $date['week_ratio']['order_rate'] = $this->calculateRate($date['week_ratio']['recent_orders'],$date['week_ratio']['last_orders']);


        //本月与上月
        $date['month_ratio']['last_sales'] = $this->orderFinishMoney('last month');
        $date['month_ratio']['recent_sales'] = $this->orderFinishMoney('month');

        $date['month_ratio']['sale_rate'] = (int)$this->calculateRate($date['month_ratio']['recent_sales'],$date['month_ratio']['last_sales']);


        $date['month_ratio']['last_orders'] = $this->orderFinishTotal('last month');
        $date['month_ratio']['recent_orders'] = $this->orderFinishTotal('month');

        $date['month_ratio']['order_rate'] = $this->calculateRate($date['month_ratio']['recent_orders'],$date['month_ratio']['last_orders']);
        //今年与去年
        $date['year_ratio']['last_sales'] = $this->orderFinishMoney('last year');
        $date['year_ratio']['recent_sales'] = $this->orderFinishMoney('year');

        $date['year_ratio']['sale_rate'] = (int)$this->calculateRate($date['year_ratio']['recent_sales'],$date['year_ratio']['last_sales']);
        $date['year_ratio']['last_orders'] = $this->orderFinishTotal('last year');
        $date['year_ratio']['recent_orders'] = $this->orderFinishTotal('year');

        $date['year_ratio']['order_rate'] = $this->calculateRate($date['year_ratio']['recent_orders'],$date['year_ratio']['last_orders']);

        return $date;


    }

    /**
     * 计算增长率
     */
    protected function calculateRate($recent,$last){
        $recent = str_replace(',', '', $recent);
        $last = str_replace(',', '', $last);
        $last == 0 ? $rate = $recent : $rate = ($recent-$last)/$last;
        $value = sprintf("%.2f", $rate*100);
        return $value;
    }

//========================================================================
}
