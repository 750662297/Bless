package com.example.blessunleashed;

import org.mybatis.spring.annotation.MapperScan;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.context.annotation.ComponentScan;

@SpringBootApplication
@MapperScan("com.example.blessunleashed.dao")
public class BlessunleashedApplication {

    public static void main(String[] args) {
        SpringApplication.run(BlessunleashedApplication.class, args);
    }

}
