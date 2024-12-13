<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Chirp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_un_utilisateur_peut_creer_un_chirp(): void
    {
        $this->withoutExceptionHandling();

        // Simuler un utilisateur connecté
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => 'Mon premier chirp !'
            ]);

        // Vérifier que le chirp a été ajouté à la base de données
        $reponse->assertStatus(302);
        $this->assertDatabaseHas('chirps', [
        'message' => 'Mon premier chirp !',
        'user_id' => $utilisateur->id,
        ]);    
    }



    public function test_un_chirp_ne_peut_pas_avoir_un_contenu_vide()
    {
    $utilisateur = User::factory()->create();
    $this->actingAs($utilisateur);
    $reponse = $this->post('/chirps', [
        'message' => ''
    ]);
    $reponse->assertSessionHasErrors(['message']);
    }
    public function test_un_chirp_ne_peut_pas_depasse_255_caracteres()
    {
    $utilisateur = User::factory()->create();
    $this->actingAs($utilisateur);
    $reponse = $this->post('/chirps', [
    'message' => str_repeat('a', 256)
    ]);
    $reponse->assertSessionHasErrors(['message']);
    }



    public function test_les_chirps_sont_affiches_sur_la_page_d_accueil()
    {
    
    $this->withoutExceptionHandling();

    $utilisateur = User::factory()->create();
    $this->actingAs($utilisateur);

    $chirps = Chirp::factory()->count(3)->create();
    $reponse = $this->get('/dashboard');
    foreach ($chirps as $chirp) {
    $reponse->assertSee($chirp->message);
    }
    }



    public function test_un_utilisateur_peut_modifier_son_chirp()
    {
    $utilisateur = User::factory()->create();
    $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
    $this->actingAs($utilisateur);
    $reponse = $this->put("/chirps/{$chirp->id}", [
    'message' => 'Chirp modifié'
    ]);
    $reponse->assertStatus(302);
    // Vérifie si le chirp existe dans la base de donnée.
    $this->assertDatabaseHas('chirps', [
    'id' => $chirp->id,
    'message' => 'Chirp modifié',
    ]);
    }


    public function test_un_utilisateur_peut_supprimer_son_chirp()
    {
    $utilisateur = User::factory()->create();
    $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
    $this->actingAs($utilisateur);
    $reponse = $this->delete("/chirps/{$chirp->id}");
    $reponse->assertStatus(302);
    $this->assertDatabaseMissing('chirps', [
    'id' => $chirp->id,
    ]);
    }


    public function test_un_utilisateur_ne_peut_pas_modifier_le_chirp_d_un_autre_utilisateur(){
        $utilisateur1 = User::factory()->create();
        $utilisateur2 = User::factory()->create();

        //creer un chirp avec utilisateur1
        $chirp = Chirp::factory()->create(['user_id' => $utilisateur1->id]);

        // essayer de modifier le chirp avec utilisateur2
        $reponse = $this->actingAs($utilisateur2)->put("/chirps/{$chirp->id}", [
            'message' => 'Nouveau message'
        ]);
        $reponse->assertStatus(403);
    }
    public function test_un_utilisateur_ne_peut_pas_supprimer_le_chirp_d_un_autre_utilisateur(){
        $utilisateur1 = User::factory()->create();
        $utilisateur2 = User::factory()->create();

        $chirp = Chirp::factory()->create(['user_id' => $utilisateur1->id]);

        // essayer de supprimer le chirp avec utilisateur2
        $reponse = $this->actingAs($utilisateur2)->delete("/chirps/{$chirp->id}", [
            'message' => 'Nouveau message'
        ]);
        $reponse->assertStatus(403);
    }

    public function test_validation_pour_la_mise_a_jour_dun_chirp()
    {
    $utilisateur = User::factory()->create();
    $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);

    // Test du message vide
    $reponse = $this->actingAs($utilisateur)->put("/chirps/{$chirp->id}", [
        'message' => ''
    ]);
    $reponse->assertSessionHasErrors('message');

    // Test du message trop long
    $reponse = $this->actingAs($utilisateur)->put("/chirps/{$chirp->id}", [
        'message' => str_repeat('a', 256)
    ]);
    $reponse->assertSessionHasErrors('message');
    }


    public function test_limitation_du_nombre_de_chirps_par_utilisateur()
    {
        $utilisateur = User::factory()->create();
    
        // creer 10 chirps
        Chirp::factory()->count(10)->create(['user_id' => $utilisateur->id]);
    
        // essayer de créer un 11è chirps
        $reponse = $this->actingAs($utilisateur)->post('/chirps', [
            'message' => 'Un autre chirp'
        ]);
        
        $reponse->assertSessionHasErrors('message'); 
    }


    public function test_filtrage_des_chirps_par_date()
    {
    $this->withoutExceptionHandling();

    $utilisateur = User::factory()->create();
    $this->actingAs($utilisateur);

    // Créer un Chirp récent
    $recentChirp = Chirp::factory()->create([
        'user_id' => $utilisateur->id,
        'created_at' => now()
    ]);

    // Créer un Chirp ancien
    $oldChirp = Chirp::factory()->create([
        'user_id' => $utilisateur->id,
        'created_at' => now()->subDays(8)
    ]);

    // Vérifier que seulement le chirp récent est visible
    $reponse = $this->get('/');
    $reponse->assertSee($recentChirp->message);
    $reponse->assertDontSee($oldChirp->message);
    }

}
