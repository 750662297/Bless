package com.example.blessunleashed.controller;
import com.example.blessunleashed.contract.NameTest;
import com.example.blessunleashed.service.INameService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/NAME")
public class NameController {
    @Autowired
    private INameService iNameService;
    @PostMapping("queryList")
    public String queryList(@RequestBody NameTest nameTest){
        iNameService.select();
        return nameTest.getName();
    }
}
