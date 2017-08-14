<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pages')->insert([
            'hasimage' => 0,
            'header' => 1,
            'footer' => 2,
        ]);

        DB::table('page_translations')->insert([
            'page_id' => 1,
            'locale' => 'en',
            'slug' => 'about',
            'seo_title' => 'About Dentacoin',
            'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
            'title' => 'About',
            'content' => '[{"type":"html","content":"<h1>Dentacoin Trusted Reviews Platform is the pilot tool of Dentacoin mission, invented to serve and improve the global dental community.</h1>\n\n<div>Dentacoin Trusted Review Platform is one of the value-generating tools, invented to serve the mission of Dentacoin: to improve dental care worlwide and make it affordable. This is the first Blockchain-based platform for trusted dental treatment reviews which allows patients to raise their voice and thus - to have a strong influence on the overall treatment quality in the industry. Simultaneously, it offers dentists access to up-to-date, extremely valuable market research data and qualified patient feedback - the most powerful tool to improve service quality and to establish a loyal patient base.</div>\n\n<div> </div>\n\n<div>This is the first Blockchain-based platform for trusted dental treatment reviews which allows patients to raise their voice and thus - to have a strong influence on the overall treatment quality in the industry. Simultaneously, it offers dentists access to up-to- date, extremely valuable market research data and qualified patient feedback - the most powerful tool to improve service quality and to establish a loyal patient base.</div>\n\n<div> </div>\n\n<div>Both individuals and dentists get rewarded with Dentacoins, corresponding to the amount of value they have brought through their direct input. These Dentacoins are added to the total circulating supply and become part of the global economy.</div>\n\n<div> </div>\n\n<div>This community respects and insists on:</div>\n","padding":"small","background":""},{"type":"html-4","columns":[{"content":"<h2>No censorship</h2>\nDue to the transparent, incentive-based and censorship resistant nature of this Blockchain-based solution, Dentacoin Review Platform aims at being the most functional review and market research system. Through a self-executing Ethereum Smart Contract, the Dentacoin review platform assures optimal autonomy, trust, speed and safety.","background":""},{"content":"<h2>Rewarded feedback</h2>\nPatients are able to find their dentists in order to write a review based on open and multiple-choice questions and optionally &ndash; answer market research questions. Each survey is linked to the Ethereum blockchain and paid for in Dentacoins by a Smart Contract. In return, the earned amount can be used to pay for part of the further treatments at one of the Dentacoin partner dentists/clinics.","background":""},{"content":"<h2>Trusted Reviews</h2>\nIn order to provide even more trustworthy customer reviews, a new notion of &bdquo;trusted reviews&ldquo; is implemented. Thus, there is a difference between standard reviews (which generally may be written by anyone) and trusted reviews on the other hand, which can only be written by actual patients. To make sure that a reviewer writes the feedback based on a prior treatment, each partner dentist is able to invite patients via email containing a review request link. This review is then marked as a trusted review.","background":""},{"content":"<h2>Global Community</h2>\nBy providing ways of earning and spending Dentacoins, we distribute the dental currency all over the industry. And even better: we harvest profound knowledge of the industry, allowing dentists and patients to form strong communities around the world and build upon mutual interest.","background":""}],"padding":"small","background":""}]',
        ]);

    }
}