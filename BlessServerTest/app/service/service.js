const {Service} = require('egg');

class DBService extends Service {
    constructor(ctx) {
        super(ctx);
        this.DBglobalDBCreate = this.app.mssql.get('globalDBCreate');
        this.DBgamedbPC = this.app.mssql.get('gamedbPC');
    };

    // 在 Service 中定义需要的方法
    async queryUserFromDB1(username,password){
        const result = await this.DBglobalDBCreate.query(`SELECT * FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='${username}' AND accountPassword='${password}'`);

        return result;
    }

    async queryCharByUserName(username){
        const result = await this.DBgamedbPC.query(`SELECT DB_ID,Player_Name FROM create_gamedb_pc.dbo.DBPlayer WHERE USN = (SELECT usn FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='${username}') AND Unreg_Flag=0`);

        return result;
    }

    async getUserMoney(username){
        let cash;
        console.log(username)
        const result = await this.DBglobalDBCreate.query(`SELECT uCash FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='${username}'`);
        console.log("getUserMoney   :",result)
        
        if(result.recordset.length !=0)
        {
            cash = result.recordset[0].uCash;
        }
        
        return cash;
    }

    async MinusUserCash(username, cash){
        let isSuccess=true;
        const result = await this.DBglobalDBCreate.query(`UPDATE GlobalDB_Create.dbo.Web_Account SET uCash='${cash}' WHERE accountName='${username}'`);
        if(result.rowsAffected ==0)
        {
            isSuccess=false
        }
        return isSuccess;
    }

    async BuyItem(charId, str){
        const result = await this.DBgamedbPC.query(`EXEC create_gamedb_pc.dbo.BLSP_Native_CreateMail ${charId},0,0,'系统',253,1,'WEB商城','[]',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0${str},'2079-01-01 00:00:00','2079-01-01 00:00:00',NULL`);
        return result;
    }

    async InsertBuyHistory(username,charId,index,price,cash){

        const result = await this.DBgamedbPC.query(`INSERT INTO create_gamedb_pc.WebMall.TBL_MallBuyHistory (FLD_USER_NAME, FLD_CHAR_ID, FLD_INDEX, FLD_IP, FLD_PRICE, FLD_BALANCE) VALUES (${username}, ${charId}, ${index}, '192.168.200.93', ${price}, ${cash})`);
        console.log("InsertBuyHistory    end")
        console.log("InsertBuyHistory   result:",result)
        return result;
    }
}

    
module.exports = DBService;