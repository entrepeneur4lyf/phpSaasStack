<?php

namespace Src\Models;

use Src\Core\Database;

class Profile extends User
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getProfileByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function updateProfile($userId, $profileData)
    {
        $stmt = $this->db->prepare("
            UPDATE profiles 
            SET bio = ?, website = ?, social_media = ?, avatar_url = ?, location = ?, skills = ?
            WHERE user_id = ?
        ");
        return $stmt->execute([
            $profileData['bio'],
            $profileData['website'],
            json_encode($profileData['social_media']),
            $profileData['avatar_url'],
            $profileData['location'],
            $profileData['skills'],
            $userId
        ]);
    }

    public function getBadges($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_badges WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getSkills($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_skills WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getOfferedCategories($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_categories WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getReviews($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM user_reviews WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getSalesChartData($userId)
    {
        // Implement the logic to get sales chart data
        // This might involve joining with order tables and aggregating data
    }

    public function getSalesChartLabels($userId)
    {
        // Implement the logic to get sales chart labels
        // This might involve generating date labels for the chart
    }

    public function getRatingDistribution($userId)
    {
        // Implement the logic to get rating distribution
        // This might involve querying the reviews table and grouping by rating
    }

    // You can add more profile-specific methods here
}