<?php

if (!function_exists('validate_email_deliverable')) {
    /**
     * Validate email deliverability (DNS + format check)
     * 
     * @param string $email
     * @return array ['valid' => bool, 'reason' => string]
     */
    function validate_email_deliverable(string $email): array
    {
        // Basic format check
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'reason' => 'Format email tidak valid'];
        }

        // Extract domain
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return ['valid' => false, 'reason' => 'Format email salah'];
        }

        $domain = $parts[1];

        // Check for common disposable email domains
        $disposableDomains = [
            'tempmail.com', 'guerrillamail.com', '10minutemail.com',
            'mailinator.com', 'throwaway.email', 'temp-mail.org'
        ];

        if (in_array(strtolower($domain), $disposableDomains)) {
            return ['valid' => false, 'reason' => 'Disposable email tidak diperbolehkan'];
        }

        // Check DNS MX record
        if (!checkdnsrr($domain, 'MX')) {
            return ['valid' => false, 'reason' => "Domain {$domain} tidak memiliki MX record"];
        }

        // All checks passed
        return ['valid' => true, 'reason' => 'Email valid'];
    }
}

if (!function_exists('is_email_blacklisted')) {
    /**
     * Check if email is in blacklist
     */
    function is_email_blacklisted(string $email): bool
    {
        $db = \Config\Database::connect();
        
        $result = $db->table('email_blacklist')
            ->where('email', $email)
            ->orWhere('domain', substr(strrchr($email, "@"), 1))
            ->countAllResults();
        
        return $result > 0;
    }
}

if (!function_exists('add_to_email_blacklist')) {
    /**
     * Add email to blacklist
     */
    function add_to_email_blacklist(string $email, string $reason = 'Bounce'): bool
    {
        $db = \Config\Database::connect();
        
        $data = [
            'email' => $email,
            'domain' => substr(strrchr($email, "@"), 1),
            'reason' => $reason,
            'blacklisted_at' => date('Y-m-d H:i:s')
        ];
        
        return $db->table('email_blacklist')->insert($data);
    }
}