'use strict';

/** @type Egg.EggPlugin */
module.exports = {
  // had enabled by egg
  // static: {
  //   enable: true,
  // }
  cors:{
    enable:true,
    package:'egg-cors'
  },
  mssql:{
    enable:true,
    package:'egg-mssql'
  },
  jwt:{
    enable:true,
    package:'egg-jwt'
  }
};
