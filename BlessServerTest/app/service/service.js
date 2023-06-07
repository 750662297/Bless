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
}

    
module.exports = DBService;