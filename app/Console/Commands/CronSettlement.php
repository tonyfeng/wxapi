<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use App\Models\PayorderModel;
use App\Models\SettlementModel;
use App\Libs\Common;
use Illuminate\Console\Command;

class CronSettlement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:settlement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'every day order data settlement';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "start settlement data\t".date("Y-m-d H:i:s")."\n";

        //读出前天流水并统计
        $data = array();
        $starttime = date("Y-m-d",strtotime("-1 day"));
        $pagesize = 1000;
        $pkid = 0;
        do{
            $result = PayorderModel::orderLists($starttime,$pagesize,$pkid);
            $lists = $result->toArray();
            $count = count($lists);
            if($count > 0){

                foreach ($lists as $key=>$val){

                    $mid = $val['mid'];
                    $callback_total_fee = $val['callback_total_fee'];
                    $amount = isset($data[$mid]['amount']) ? $data[$mid]['amount'] + 1 : 1;
                    $total_fee = isset($data[$mid]['total_fee']) ? $data[$mid]['total_fee'] + $callback_total_fee : $callback_total_fee;
                    $row = array(
                        "mid"=>$mid,
                        "wx_mch_id"=>$val['wx_mch_id'],
                        "amount"=>$amount,
                        "total_fee"=>$total_fee,
                    );
                    $data[$mid] = $row;
                }
                $pkid = $lists[$count - 1]["id"];
            }
        }while($count > 0);

        //数据是否为空
        if(!$data){
            echo "not settlement data\t".date("Y-m-d H:i:s")."\n";
            exit();
        }

        //写入settlement表
        $settlementDate = $starttime;
        foreach ($data as $key=>$val){
            $mid  = $key;
            $total_fee = (float)$val['total_fee'] / 100;
            $val['settlement_total_fee'] = $total_fee - $total_fee * 0.006;
            $val['poundage_total_fee'] = $total_fee * 0.006;
            $val['settlement_date'] = $settlementDate;

            //查找是否存在
            $result = SettlementModel::settlementQuery($mid,$settlementDate);
            $lists = $result->toArray();
            $count = count($lists);
            if($count > 0){ // 更新
                $affectedRows = SettlementModel::where("mid","=",$mid)->where("settlement_date","=",$settlementDate)->update($val);
                if(!$affectedRows) {
                    Common::writeLogs('error',20016,"update table settlement".\GuzzleHttp\json_encode($val));
                }
            }else{
                $res = SettlementModel::Create($val);
                if(!$res){
                    Common::writeLogs('error',20016,"insert table settlement".\GuzzleHttp\json_encode($val));
                }
            }
        }
        echo "end settlement data\t".date("Y-m-d H:i:s")."\n";
    }
}
