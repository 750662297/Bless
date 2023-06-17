const { Service } = require('egg');

class DBService extends Service {
    constructor(ctx) {
        super(ctx);
        this.DBglobalDBCreate = this.app.mssql.get('globalDBCreate');
        this.DBgamedbPC = this.app.mssql.get('gamedbPC');
    };

    // 在 Service 中定义需要的方法
    async queryUserFromDB1(username, password) {
        const result = await this.DBglobalDBCreate.query(`SELECT * FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='${username}' AND accountPassword='${password}'`);

        return result;
    }

    async queryCharByUserName(username) {
        const result = await this.DBgamedbPC.query(`SELECT DB_ID,Player_Name FROM create_gamedb_pc.dbo.DBPlayer WHERE USN = (SELECT usn FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='${username}') AND Unreg_Flag=0`);

        return result;
    }

    async getUserMoney(username) {
        let cash;
        const result = await this.DBglobalDBCreate.query(`SELECT uCash FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='${username}'`);

        if (result.recordset.length != 0) {
            cash = result.recordset[0].uCash;
        }

        return cash;
    }

    async MinusUserCash(username, cash) {
        let isSuccess = true;

        let result;
        try {
            result = await this.DBglobalDBCreate.query(`UPDATE GlobalDB_Create.dbo.Web_Account SET uCash='${cash}' WHERE accountName='${username}'`);
        } catch (error) {
            console.log(error)
        }

        if (result.rowsAffected == 0) {
            isSuccess = false
        }
        
        return isSuccess;
    }

    async BuyItem(charId, str) {

        let result;
        try {
            result = await this.DBgamedbPC.query(`EXEC create_gamedb_pc.dbo.BLSP_Native_CreateMail '${charId}',0,0,'系统',253,1,'WEB商城','[]',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0${str},'2079-01-01 00:00:00','2079-01-01 00:00:00',NULL`);
        
        } catch (error) {
            console.log(error)
        }
        return result;
    }

    async InsertBuyHistory(username, charId, price, cash) {

        let result;
        try {
            result = await this.DBgamedbPC.query(`INSERT INTO create_gamedb_pc.WebMall.TBL_MallBuyHistory (FLD_USER_NAME, FLD_CHAR_ID, FLD_PRICE, FLD_BALANCE) VALUES ('${username}', '${charId}', '${price}', '${cash}')`);
        } catch (err) {
            console.log(err)
        }

        return result;
    }

    async InsertTemp(name, item_id, price, num, icon_url, showName) {
        console.log(item_id)
        showName = name + '*' + num;
        icon_url = "../../public/images/item_icon/Icon_801170.png";
        let result;

        let re = await this.DBglobalDBCreate.query(`SELECT * FROM GlobalDB_Create.dbo.BL_StoreItems WHERE itemId='${item_id}'`)
        if (re.rowsAffected == 1) {
            result = "continue";
            return result;
        }
        result = await this.DBglobalDBCreate.query(`INSERT INTO GlobalDB_Create.dbo.BL_StoreItems (itemId, name,  price, iconUrl, showName, num) VALUES ('${item_id}', '${name}', '${price}', '${icon_url}', '${showName}', '${num}')`);
        console.log("result end")
        console.log(result)
        return result;
    }

    async getTotalCount(name, tag) {
        let sql = `SELECT COUNT(*) AS totalRowCount FROM GlobalDB_Create.dbo.BL_StoreItems WHERE moneyType=1`;
        sql += name ? ` AND name LIKE '%${name}%'` : '';
        sql += tag ? ` AND tag='${tag}'` : '';

        let result = await this.DBglobalDBCreate.query(sql);
        console.log(result)

        return result
    }
    async queryItemList(pageSize, offset, tag, name) {
        let sql = `SELECT * from GlobalDB_Create.dbo.BL_StoreItems WHERE moneyType=1`;
        sql += name ? ` AND name LIKE '%${name}%'` : '';
        sql += tag ? ` AND tag='${tag}'` : '';
        sql += ` ORDER BY itemId OFFSET ${offset} ROWS FETCH NEXT ${pageSize} ROWS ONLY`;

        let result = await this.DBglobalDBCreate.query(sql);

        return result;
    }

    async updateItemInfo(itemId, name, price, num, tag, desc) {
        let sql = `UPDATE GlobalDB_Create.dbo.BL_StoreItems SET moneyType=1`;
        sql += name ? `, name='${name}'` : '';
        sql += `, price='${price}'`;
        sql += `, num=${num}`;
        sql += tag ? `, tag='${tag}'` : '';
        sql += desc ? `, \`desc\`='${desc}'` : '';
        sql += ` WHERE itemId='${itemId}'`;

        let result
        try {
            result = await this.DBglobalDBCreate.query(sql);
        } catch (error) {
            console.log(error)
        }

        return result;
    }

    async getItemInfo(itemId) {
        console.log("before getItemInfo")
        const result = await this.DBglobalDBCreate.query(`SELECT * from GlobalDB_Create.dbo.BL_StoreItems WHERE itemId='${itemId}'`);
        return result;
    }
}


module.exports = DBService;