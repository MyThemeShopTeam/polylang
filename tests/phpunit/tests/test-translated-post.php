<?php

class Translated_Post_Test extends PLL_UnitTestCase {

	static function wpSetUpBeforeClass() {
		parent::wpSetUpBeforeClass();

		self::create_language( 'en_US' );
		self::create_language( 'fr_FR' );
		self::create_language( 'de_DE_formal' );
	}

	function test_post_language() {
		$post_id = $this->factory->post->create();
		self::$polylang->model->post->set_language( $post_id, 'fr' );

		$this->assertEquals( 'fr', self::$polylang->model->post->get_language( $post_id )->slug );
	}

	function test_post_translation() {
		$en = $this->factory->post->create();
		self::$polylang->model->post->set_language( $en, 'en' );

		$fr = $this->factory->post->create();
		self::$polylang->model->post->set_language( $fr, 'fr' );

		$de = $this->factory->post->create();
		self::$polylang->model->post->set_language( $de, 'de' );

		self::$polylang->model->post->save_translations( $en, compact( 'en', 'fr', 'de' ) );

		$this->assertEquals( self::$polylang->model->post->get_translation( $en, 'en' ), $en );
		$this->assertEquals( self::$polylang->model->post->get_translation( $fr, 'fr' ), $fr );
		$this->assertEquals( self::$polylang->model->post->get_translation( $fr, 'en' ), $en );
		$this->assertEquals( self::$polylang->model->post->get_translation( $en, 'fr' ), $fr );
		$this->assertEquals( self::$polylang->model->post->get_translation( $de, 'fr' ), $fr );
	}

	function test_delete_post_translation() {
		$en = $this->factory->post->create();
		self::$polylang->model->post->set_language( $en, 'en' );

		$fr = $this->factory->post->create();
		self::$polylang->model->post->set_language( $fr, 'fr' );

		$de = $this->factory->post->create();
		self::$polylang->model->post->set_language( $de, 'de' );

		self::$polylang->model->post->save_translations( $en, compact( 'en', 'fr', 'de' ) );
		self::$polylang->model->post->delete_translation( $fr );

		$this->assertEquals( self::$polylang->model->post->get_translation( $fr, 'fr' ), $fr );
		$this->assertEquals( self::$polylang->model->post->get_translation( $en, 'de' ), $de );
		$this->assertEquals( self::$polylang->model->post->get_translation( $de, 'en' ), $en );

		$this->assertFalse( self::$polylang->model->post->get_translation( $en, 'fr' ) ); // fails
		$this->assertFalse( self::$polylang->model->post->get_translation( $fr, 'en' ) );
		$this->assertFalse( self::$polylang->model->post->get_translation( $fr, 'de' ) );
		$this->assertFalse( self::$polylang->model->post->get_translation( $de, 'fr' ) ); // fails
	}

	function test_current_user_can_synchronize() {
		add_filter( 'pll_pre_current_user_can_synchronize_post', '__return_null' ); // Enable capability check

		$editor = self::factory()->user->create( array( 'role' => 'editor' ) );
		$author = self::factory()->user->create( array( 'role' => 'author' ) );

		wp_set_current_user( $author );

		$en = $this->factory->post->create();
		self::$polylang->model->post->set_language( $en, 'en' );

		$fr = $this->factory->post->create();
		self::$polylang->model->post->set_language( $fr, 'fr' );

		self::$polylang->model->post->save_translations( $en, compact( 'en', 'fr' ) );

		$this->assertTrue( self::$polylang->model->post->current_user_can_synchronize( $en ) );
		$this->assertTrue( self::$polylang->model->post->current_user_can_synchronize( $fr ) );

		wp_set_current_user( $editor );

		$this->assertTrue( self::$polylang->model->post->current_user_can_synchronize( $en ) );
		$this->assertTrue( self::$polylang->model->post->current_user_can_synchronize( $fr ) );

		$de = $this->factory->post->create();
		self::$polylang->model->post->set_language( $de, 'de' );

		self::$polylang->model->post->save_translations( $en, compact( 'en', 'fr', 'de' ) );

		wp_set_current_user( $editor );

		$this->assertTrue( self::$polylang->model->post->current_user_can_synchronize( $en ) );
		$this->assertTrue( self::$polylang->model->post->current_user_can_synchronize( $fr ) );
		$this->assertTrue( self::$polylang->model->post->current_user_can_synchronize( $de ) );

		wp_set_current_user( $author );

		$this->assertFalse( self::$polylang->model->post->current_user_can_synchronize( $en ) );
		$this->assertFalse( self::$polylang->model->post->current_user_can_synchronize( $fr ) );
		$this->assertFalse( self::$polylang->model->post->current_user_can_synchronize( $de ) );
	}
}
