<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //1
    	DB::table('email_templates')->insert([
	        'name' => 'Registration - Dentist',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 1,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

Also a

[verifylink]Verification link[/verifylink]

[i]Thanks![/i]',
            'locale' => 'en',
        ]);

        //2
    	DB::table('email_templates')->insert([
	        'name' => 'Registration - User',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 2,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

Also a

[verifylink]Verification link[/verifylink]

[i]Thanks![/i]',
            'locale' => 'en',
        ]);

        //3
    	DB::table('email_templates')->insert([
	        'name' => 'Verification - Dentist',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 3,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

[i]Thanks![/i]',
            'locale' => 'en',
        ]);

        //4
    	DB::table('email_templates')->insert([
	        'name' => 'Verification - User',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 4,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

[i]Thanks![/i]',
            'locale' => 'en',
        ]);

        //5
    	DB::table('email_templates')->insert([
	        'name' => 'Recover Password',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 5,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

Also a

[recoverlink]Recover link[/recoverlink]

[i]Thanks![/i]',
            'locale' => 'en',
        ]);

        //6
    	DB::table('email_templates')->insert([
	        'name' => 'New review',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 6,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

Also a rating: [rating] , author name: [author_name], dentist name: [dentist_name] and a

[reviewlink]Review link[/reviewlink]

[i]Thanks![/i]',
            'locale' => 'en',
        ]);

        //7
        DB::table('email_templates')->insert([
            'name' => 'Review Invitation',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 7,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

Also a

[invitelink]Invitation link[/invitelink]

[i]Thanks![/i]',
            'locale' => 'en',
        ]);

        //8
        DB::table('email_templates')->insert([
            'name' => 'Reply from Dentist',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 8,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

Also a rating: [rating] , author name: [author_name], dentist name: [dentist_name] and a

[reviewlink]Review link[/reviewlink]

[i]Thanks![/i]',
            'locale' => 'en',
        ]);


        //9
        DB::table('email_templates')->insert([
            'name' => 'Dentist Profile Created By User',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 9,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

Also who invited him/her: [inviter_name] and a

[claimlink]Review link[/claimlink]

[i]Thanks![/i]',
            'locale' => 'en',
        ]);

        //10
    	DB::table('email_templates')->insert([
	        'name' => 'Dentist Profile Claimed',
        ]);
        DB::table('email_template_translations')->insert([
            'email_template_id' => 10,
            'subject' => 'The subject line',
            'title' => 'Email main title',
            'subtitle' => 'Optional subtitle',
            'content' => 'Hello [name],

[h2]Heading[/h2]

This is the email content. [b]Part[/b] of it has some [u]formatting[/u].

[i]Thanks![/i]',
            'locale' => 'en',
        ]);

    }
}
