<?php

namespace App\Observers;

use App\Models\VoxQuestion;

class VoxQuestionObserver {

    public function created(VoxQuestion $voxQuestion) {

        $vox = $voxQuestion->vox;

        if($vox->type == 'normal') {
            $vox->load('questions');
            $vox->questions_count = $vox->questions->count();
            $vox->save();
        }
    }
}