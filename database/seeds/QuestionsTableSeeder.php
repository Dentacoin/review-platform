<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class QuestionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $i=1;;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'Please, go back to your communication (by phone or online) with {name} before your appointment. How would you describe it?',
            'label' => 'Pre-treatment communication',
            'options' => json_encode([
                ['Slow and irresponsive service', 'Fast and responsive service'],
                ['Not informative at all', 'I enjoyed it'],
                ['Absolutely useless', 'Extremely useful'],
                ['I felt tortured', 'Higly informative'],
            ]),
            'locale' => 'en'
        ]);

        $i++;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'When you first came in the clinic what was your initial impression about the ambience?',
            'label' => 'Ambience',
            'options' => json_encode([
                ['Simple', 'Exceptional'],
                ['Absolutely uncomfortable', 'Extremely comfortable'],
                ['Unacceptably dirty', 'Super high hygiene'],
                ['Not professional at all', 'Highly professional'],
            ]),
            'locale' => 'en'
        ]);

        $i++;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'How would you describe the the Receptionist/ the Patient Relations Team?',
            'label' => 'Reception',
            'options' => json_encode([
                ['Completely incompetent', 'Extremely competent'],
                ['Very rude', 'Supremely polite'],
                ['Absolutely unpresentable', 'Outstanding personality/ies'],
                ['Not interested in me at all', 'Always ready to listen and help'],
            ]),
            'locale' => 'en'
        ]);

        $i++;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'How would you describe your experience with the doctor?',
            'label' => 'Doctor',
            'options' => json_encode([
                ['The doctor was absolutely incompetent', 'The doctor earned my full trust from the very beginning'],
                ['The doctor was very rude', 'The doctor was supremely polite'],
                ['The doctor couldn\'t earn my trust at all', 'The doctor was outstandingly competent'],
                ['I was sure the doctor could not find solution to my problems', 'I was sure the doctor would easily find the best solution to my problems'],
            ]),
            'locale' => 'en'
        ]);

        $i++;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'How would you rate the dental nurse/s?',
            'label' => 'Dental nurse',
            'options' => json_encode([
                ['Absolutely unhelpful', 'Extremely helpful'],
                ['Very rude', 'Supremely polite'],
                ['Not interested in me at all', 'Exceptionally caring'],
                ['Kept me under stress', 'Made me feel relaxed'],
            ]),
            'locale' => 'en'
        ]);

        $i++;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'How did you feel during the treatment?',
            'label' => 'Treatment experience',
            'options' => json_encode([
                ['Terrified', 'Relaxed'],
                ['Disappointed by the poor technologies', 'Fascinated by the modern technologies'],
                ['I felt like talking to a wall', 'I felt all my needs and desires were taken into account'],
                ['I was sure I made a wrong choice', 'I was sure I made the best choice'],
            ]),
            'locale' => 'en'
        ]);

        $i++;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'How would you describe the overall treatment quality?',
            'label' => 'Treatment quality',
            'options' => json_encode([
                ['The treatment didn\'t meet my needs and expectations at all', 'The treatment plan met my needs and expectations completely'],
                ['Disappointing treatment results', 'Fascinating treatment results'],
            ]),
            'locale' => 'en'
        ]);

        $i++;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'How would you describe the overall communication quality with the team?',
            'label' => 'Communication quality',
            'options' => json_encode([
                ['The treatment plan wasn\'t explained to me', 'The treatment plan was thoroughly explained to me in all details - treatment options, risks/advantages, costs'],
                ['They tried to convince me in a treatment I don\'t need', 'They helped me choose the best treatment option'],
                ['I didn\'t understand a word of what they were saying', 'I easily understood every single explanation'],
                ['They didn\'t listen to me at all', 'They listened to every word of mine'],
            ]),
            'locale' => 'en'
        ]);

        $i++;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'How would you describe your billing experience?',
            'label' => 'Billing experience',
            'options' => json_encode([
                ['Vastly negative', 'Highly positive'],
                ['I felt as if I was robbed', 'I felt it was worth every cent'],
            ]),
            'locale' => 'en'
        ]);

        $i++;
        DB::table('questions')->insert([
            'order' => $i
        ]);
        DB::table('question_translations')->insert([
            'question_id' => $i,
            'question' => 'What is your overall impression about {name}?',
            'label' => 'Overall impression',
            'options' => json_encode([
                ['I am disappointed by the medical team incompetence', 'I am impressed by the medical team competence'],
                ['The service was beneath criticism', 'The service was extraordinary'],
                ['The most old-fashioned equipment possible', 'The latest technologies embedded'],
                ['I am convinced this is the worst clinic in the world', 'I believe this is the best clinic in the world'],
            ]),
            'locale' => 'en'
        ]);

    }
}
