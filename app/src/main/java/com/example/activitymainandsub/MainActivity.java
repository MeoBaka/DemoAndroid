package com.example.activitymainandsub;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageButton;

public class MainActivity extends AppCompatActivity {

    ImageButton btnBongDa;
    ImageButton btnGmail;
    ImageButton btnGoogle;
    ImageButton btnFacebook;
    ImageButton btnCall;
    ImageButton btnSms;

    void Connect(){
        btnCall = (ImageButton) findViewById(R.id.btnCallid);
        btnSms = (ImageButton )findViewById(R.id.btnSmsid);
        btnBongDa = (ImageButton )findViewById(R.id.btnBongDa);
        btnGmail = (ImageButton )findViewById(R.id.btnGmail);
        btnGoogle = (ImageButton )findViewById(R.id.btnGoogle);
        btnFacebook = (ImageButton )findViewById(R.id.btnFb);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        Connect();
        btnCall.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Call();
            }
        });
        btnSms.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Sms();
            }
        });
        btnBongDa.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                WebSite("https://bongdaplus.vn/");
            }
        });
        btnFacebook.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                WebSite("https://facebook.com/");
            }
        });
        btnGoogle.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                WebSite("https://google.com/");
            }
        });
        btnGmail.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                SendEmail();
            }
        });
    }
    void SendEmail(){
        Intent intentGmail = new Intent();
        intentGmail.setAction(Intent.ACTION_SEND);
        intentGmail.setData(Uri.parse("mailto:richardmai686@gmail.com"));
        intentGmail.putExtra(Intent.EXTRA_EMAIL,"mailto:");
        intentGmail.setType("text/html");
        intentGmail.putExtra(Intent.EXTRA_SUBJECT, "Tiêu đề Email");
        intentGmail.putExtra(Intent.EXTRA_TEXT, "Nội dung Email");
        startActivity(Intent.createChooser(intentGmail,"Gửi Email"));
    }


    void WebSite(String URL){
        Intent intentVNEpress = new Intent();
        intentVNEpress.setAction(Intent.ACTION_VIEW);
        intentVNEpress.setData(Uri.parse(URL));
        startActivity(intentVNEpress);
    }

    void Call(){
        Intent intentVNEpress = new Intent();
        intentVNEpress.setAction(Intent.ACTION_DIAL);
        intentVNEpress.setData(Uri.parse("tel:0933567679"));
        startActivity(intentVNEpress);
    }

    void Sms(){
        Intent intentVNEpress = new Intent();
        intentVNEpress.setAction(Intent.ACTION_SENDTO);
        intentVNEpress.setData(Uri.parse("sms:0933567679"));
        startActivity(intentVNEpress);
    }
}